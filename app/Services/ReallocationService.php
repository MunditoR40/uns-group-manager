<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Servicio Central de Reasignación de Grupos UNS
 * Desarrollado por: Angel Rojas (Tech Lead & Algoritmo de Reasignación)
 */
class ReallocationService
{
    /**
     * Regla B: Capacidad y Aforo Flexible (Gestión Manual)
     * - Capacidad base de laboratorio: 15 alumnos por grupo.
     * - Capacidad ampliada por laptop: hasta 17 alumnos (toggle has_laptop = true).
     * - Capacidad máxima por autorización docente: hasta 18 alumnos (toggle teacher_authorized = true).
     *
     * @param PracticeGroup $group
     * @return int Aforo efectivo resultante
     */
    public function calculateEffectiveCapacity(PracticeGroup $group): int
    {
        $base = (int) ($group->base_capacity ?: 15);

        $enrollments = $group->relationLoaded('enrollments')
            ? $group->enrollments
            : $group->enrollments()->get();

        $laptopCount = $enrollments->where('has_laptop', true)->count();
        $authCount = $enrollments->where('teacher_authorized', true)->count();

        $effective = $base;

        // Cada alumno con laptop amplía el aforo en +1 hasta un tope de 17
        if ($laptopCount > 0) {
            $effective = max($effective, min(17, $base + $laptopCount));
        }

        // Cada autorización docente amplía el aforo en +1 hasta un tope absoluto de 18
        if ($authCount > 0) {
            $effective = max($effective, min(18, $effective + $authCount));
        }

        return min(18, $effective);
    }

    /**
     * Regla A: Mapeo y División de Grupos (T1 -> T2)
     * - Caso 4 prácticas iniciales (P1A, P1B, P1C, P1D):
     *   Permanecen en Teoría 1: P1A, P1B
     *   Migran a Teoría 2: P1C -> P2A, P1D -> P2B
     * - Caso 5 prácticas iniciales (P1A, P1B, P1C, P1D, P1E):
     *   Permanecen en Teoría 1: P1A, P1B, P1C
     *   Migran a Teoría 2: P1D -> P2A, P1E -> P2B
     * - Regla general UNS: Si el grupo migra a T2, su teoría y asignación se actualizan
     *   automáticamente y su estado pasa a 'reasignado'.
     *
     * @param Course $course
     * @param User $executor Delegado que ejecuta la operación
     * @return array Resultado de la operación con conteos y batch_id
     */
    public function splitTheoryGroups(Course $course, User $executor): array
    {
        return DB::transaction(function () use ($course, $executor) {
            $batchId = (string) Str::uuid();

            // Garantizar existencia de Teoría 1 y Teoría 2
            $theory1 = $course->theoryGroups()->firstOrCreate(['name' => 'Teoría 1']);
            $theory2 = $course->theoryGroups()->firstOrCreate(['name' => 'Teoría 2']);

            // Obtener los grupos de práctica actuales de Teoría 1 ordenados alfabéticamente
            $practiceGroups = PracticeGroup::where('theory_group_id', $theory1->id)
                ->orderBy('code')
                ->get();

            $total = $practiceGroups->count();
            if ($total < 2) {
                return [
                    'success' => false,
                    'message' => 'Se requieren al menos 2 grupos de práctica en Teoría 1 para realizar la división.',
                    'migrated_groups' => 0,
                    'migrated_students' => 0,
                    'batch_id' => null,
                ];
            }

            // Mapeo oficial UNS:
            // Para 4 grupos: 2 quedan en T1, 2 migran a T2.
            // Para 5 grupos: 3 quedan en T1, 2 migran a T2.
            // Regla matemática: ceil(total / 2) quedan en T1, floor(total / 2) pasan a T2.
            $stayCount = (int) ceil($total / 2);
            $migratingGroups = $practiceGroups->slice($stayCount);

            $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
            $index = 0;
            $migratedStudents = 0;

            foreach ($migratingGroups as $pGroup) {
                $oldCode = $pGroup->code;
                $newCode = 'P2' . ($letters[$index] ?? chr(65 + $index));
                $index++;

                $oldTheoryId = $pGroup->theory_group_id;

                // Actualizar grupo de práctica a Teoría 2
                $pGroup->update([
                    'theory_group_id' => $theory2->id,
                    'code' => $newCode,
                ]);

                // Actualizar a los estudiantes asignados a este grupo
                $enrollments = $pGroup->enrollments()->get();
                foreach ($enrollments as $enrollment) {
                    $prevState = [
                        'theory_group_id' => $oldTheoryId,
                        'theory_name' => 'Teoría 1',
                        'practice_group_id' => $pGroup->id,
                        'practice_code' => $oldCode,
                        'status' => $enrollment->status,
                    ];

                    $enrollment->update(['status' => 'reasignado']);

                    $newState = [
                        'theory_group_id' => $theory2->id,
                        'theory_name' => 'Teoría 2',
                        'practice_group_id' => $pGroup->id,
                        'practice_code' => $newCode,
                        'status' => 'reasignado',
                    ];

                    // Registro inmutable de auditoría
                    AuditLog::create([
                        'batch_id' => $batchId,
                        'enrollment_id' => $enrollment->id,
                        'user_id' => $executor->id,
                        'action_type' => 'reallocation',
                        'previous_state' => $prevState,
                        'new_state' => $newState,
                        'description' => "División T1->T2: Migración de {$oldCode} a {$newCode}",
                        'is_reverted' => false,
                    ]);

                    $migratedStudents++;
                }
            }

            return [
                'success' => true,
                'message' => "División exitosa. Se migraron {$migratingGroups->count()} grupos y {$migratedStudents} estudiantes a Teoría 2.",
                'migrated_groups' => $migratingGroups->count(),
                'migrated_students' => $migratedStudents,
                'batch_id' => $batchId,
            ];
        });
    }

    /**
     * Regla C: Identificación de Excedentes y Vacantes por Prioridad FIFO
     * - Orden estricto por timestamp de matrícula original (enrolled_at ASC).
     * - Si un grupo sobrepasa su aforo efectivo (15, 17 o 18), los últimos inscritos pasan a la cola de excedentes.
     *
     * @param Course $course
     * @return array [ 'overflow' => [...], 'vacancies' => [...] ]
     */
    public function getOverflowAndVacancies(Course $course): array
    {
        $practiceGroups = PracticeGroup::whereHas('theoryGroup', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })
        ->with(['theoryGroup', 'enrollments' => function ($q) {
            $q->with('user')->orderBy('enrolled_at', 'asc');
        }])
        ->get();

        $overflowList = [];
        $vacanciesMap = [];

        foreach ($practiceGroups as $group) {
            $effectiveCapacity = $this->calculateEffectiveCapacity($group);
            $enrollments = $group->enrollments;
            $count = $enrollments->count();

            if ($count > $effectiveCapacity) {
                // Alumnos más allá del aforo efectivo van a la cola de excedentes
                $excessEnrollments = $enrollments->slice($effectiveCapacity);
                foreach ($excessEnrollments as $excess) {
                    $overflowList[] = [
                        'enrollment' => $excess,
                        'current_group' => $group,
                        'enrolled_at' => $excess->enrolled_at,
                    ];
                }
            } elseif ($count < $effectiveCapacity) {
                $vacanciesMap[$group->id] = [
                    'group' => $group,
                    'available_slots' => $effectiveCapacity - $count,
                ];
            }
        }

        // Orden estricto FIFO para la cola de excedentes global
        usort($overflowList, function ($a, $b) {
            return $a['enrolled_at'] <=> $b['enrolled_at'];
        });

        return [
            'overflow' => $overflowList,
            'vacancies' => $vacanciesMap,
        ];
    }

    /**
     * Regla C: Balanceo de Vacíos usando la Cola de Excedentes en orden FIFO
     * Transfiere a los alumnos excedentes hacia grupos incompletos respetando su orden de inscripción.
     *
     * @param Course $course
     * @param User $executor
     * @return array
     */
    public function balanceOverflow(Course $course, User $executor): array
    {
        return DB::transaction(function () use ($course, $executor) {
            $batchId = (string) Str::uuid();

            $data = $this->getOverflowAndVacancies($course);
            $overflow = $data['overflow'];
            $vacancies = $data['vacancies'];

            if (empty($overflow)) {
                return [
                    'success' => false,
                    'message' => 'No existen alumnos en cola de excedentes.',
                    'reallocated_count' => 0,
                    'batch_id' => null,
                ];
            }

            if (empty($vacancies)) {
                return [
                    'success' => false,
                    'message' => 'No hay grupos con vacantes disponibles para recibir excedentes.',
                    'reallocated_count' => 0,
                    'batch_id' => null,
                ];
            }

            $reallocatedCount = 0;

            foreach ($overflow as $item) {
                /** @var Enrollment $enrollment */
                $enrollment = $item['enrollment'];
                $currentGroup = $item['current_group'];

                // Buscar el primer grupo con vacante disponible que no sea el mismo grupo
                $targetGroupId = null;
                foreach ($vacancies as $gid => $vData) {
                    if ($gid != $currentGroup->id && $vData['available_slots'] > 0) {
                        $targetGroupId = $gid;
                        break;
                    }
                }

                if (!$targetGroupId) {
                    break;
                }

                $targetGroup = $vacancies[$targetGroupId]['group'];

                $prevState = [
                    'practice_group_id' => $currentGroup->id,
                    'practice_code' => $currentGroup->code,
                    'status' => $enrollment->status,
                ];

                $enrollment->update([
                    'practice_group_id' => $targetGroupId,
                    'status' => 'reasignado',
                ]);

                $newState = [
                    'practice_group_id' => $targetGroupId,
                    'practice_code' => $targetGroup->code,
                    'status' => 'reasignado',
                ];

                AuditLog::create([
                    'batch_id' => $batchId,
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $executor->id,
                    'action_type' => 'reallocation',
                    'previous_state' => $prevState,
                    'new_state' => $newState,
                    'description' => "Balanceo FIFO: Transferencia de {$currentGroup->code} a {$targetGroup->code}",
                    'is_reverted' => false,
                ]);

                $reallocatedCount++;

                // Descontar la vacante utilizada
                $vacancies[$targetGroupId]['available_slots']--;
                if ($vacancies[$targetGroupId]['available_slots'] <= 0) {
                    unset($vacancies[$targetGroupId]);
                }
            }

            return [
                'success' => true,
                'message' => "Se reubicaron {$reallocatedCount} alumnos de la cola de excedentes respetando el orden FIFO.",
                'reallocated_count' => $reallocatedCount,
                'batch_id' => $batchId,
            ];
        });
    }

    /**
     * Toggle manual de Laptop
     */
    public function toggleLaptop(Enrollment $enrollment, bool $hasLaptop, User $executor): Enrollment
    {
        $prevState = ['has_laptop' => $enrollment->has_laptop];
        $enrollment->update(['has_laptop' => $hasLaptop]);
        $newState = ['has_laptop' => $hasLaptop];

        AuditLog::create([
            'batch_id' => null,
            'enrollment_id' => $enrollment->id,
            'user_id' => $executor->id,
            'action_type' => 'laptop_toggle',
            'previous_state' => $prevState,
            'new_state' => $newState,
            'description' => 'Toggle laptop: ' . ($hasLaptop ? 'Sí' : 'No'),
            'is_reverted' => false,
        ]);

        return $enrollment;
    }

    /**
     * Toggle manual de Autorización Docente
     */
    public function toggleTeacherAuth(Enrollment $enrollment, bool $authorized, User $executor): Enrollment
    {
        $prevState = ['teacher_authorized' => $enrollment->teacher_authorized];
        $enrollment->update(['teacher_authorized' => $authorized]);
        $newState = ['teacher_authorized' => $authorized];

        AuditLog::create([
            'batch_id' => null,
            'enrollment_id' => $enrollment->id,
            'user_id' => $executor->id,
            'action_type' => 'auth_toggle',
            'previous_state' => $prevState,
            'new_state' => $newState,
            'description' => 'Toggle docente: ' . ($authorized ? 'Autorizado' : 'No Autorizado'),
            'is_reverted' => false,
        ]);

        return $enrollment;
    }

    /**
     * Movimiento manual individual
     */
    public function moveStudentManually(Enrollment $enrollment, PracticeGroup $newGroup, User $executor): Enrollment
    {
        $oldGroup = $enrollment->practiceGroup;

        $prevState = [
            'practice_group_id' => $enrollment->practice_group_id,
            'practice_code' => $oldGroup ? $oldGroup->code : null,
            'status' => $enrollment->status,
        ];

        $enrollment->update([
            'practice_group_id' => $newGroup->id,
            'status' => 'reasignado',
        ]);

        $newState = [
            'practice_group_id' => $newGroup->id,
            'practice_code' => $newGroup->code,
            'status' => 'reasignado',
        ];

        AuditLog::create([
            'batch_id' => null,
            'enrollment_id' => $enrollment->id,
            'user_id' => $executor->id,
            'action_type' => 'manual_move',
            'previous_state' => $prevState,
            'new_state' => $newState,
            'description' => "Movimiento individual manual a grupo {$newGroup->code}",
            'is_reverted' => false,
        ]);

        return $enrollment;
    }
}
