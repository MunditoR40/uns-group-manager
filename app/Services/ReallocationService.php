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
 * Servicio de Reorganización y División de Grupos UNS
 * Desarrollado por: Angel Rojas (Tech Lead)
 *
 * Automatiza:
 * 1. Verificación de condición (>= 60 alumnos matriculados).
 * 2. Creación y apertura de Teoría 2.
 * 3. Reorganización y partición generalizada de grupos de práctica (P1A, P1B... y P2A, P2B...).
 * 4. Métodos de apoyo para la gestión manual del delegado (mover estudiante, laptops, autorizaciones).
 */
class ReallocationService
{
    /** Límite de aforo estándar por práctica en laboratorios UNS */
    public const CAPACITY_LIMIT = 15;

    /**
     * Automatización central: División de Teoría y Reorganización de Grupos de Práctica
     *
     * Regla:
     * - Si el total de matriculados es >= 60:
     *   * Se parte la teoría en dos (Teoría 1 y Teoría 2).
     *   * Se reorganizan los grupos de práctica con partición truncada floor(total / 2):
     *     - Teoría 1 conserva (total - floor(total/2)) grupos: P1A, P1B, P1C...
     *     - Teoría 2 recibe floor(total / 2) grupos reiniciando en: P2A, P2B...
     *   * Los alumnos asignados a los grupos migrantes quedan vinculados a Teoría 2
     *     con estado 'reasignado' y registro en auditoría.
     * - La redistribución final de alumnos particulares se realiza de forma manual
     *   por el delegado del curso.
     *
     * @param Course $course
     * @param User $executor Delegado responsable
     * @return array
     */
    public function splitTheoryGroups(Course $course, User $executor): array
    {
        return DB::transaction(function () use ($course, $executor) {
            $batchId = (string) Str::uuid();

            // 1. Verificación de matriculados totales
            $totalEnrolled = $course->enrollments()->count();
            if ($totalEnrolled < 60) {
                return [
                    'success' => false,
                    'message' => "No se puede dividir la teoría: se requieren 60 o más alumnos matriculados (actualmente hay {$totalEnrolled}).",
                    'migrated_groups' => 0,
                    'migrated_students' => 0,
                    'batch_id' => null,
                ];
            }

            // 2. Garantizar existencia de Teoría 1 y Teoría 2
            $theory1 = $course->theoryGroups()->firstOrCreate(['name' => 'Teoría 1']);
            $theory2 = $course->theoryGroups()->firstOrCreate(['name' => 'Teoría 2']);

            // Obtener los grupos de práctica actuales de Teoría 1 ordenados alfabéticamente
            $practiceGroups = PracticeGroup::where('theory_group_id', $theory1->id)
                ->orderBy('code')
                ->get();

            $totalGroups = $practiceGroups->count();
            if ($totalGroups < 2) {
                return [
                    'success' => false,
                    'message' => 'Se requieren al menos 2 grupos de práctica en Teoría 1 para realizar la reorganización.',
                    'migrated_groups' => 0,
                    'migrated_students' => 0,
                    'batch_id' => null,
                ];
            }

            // 3. Partición generalizada truncada floor(total / 2)
            // Si son 5 grupos: floor(5/2) = 2 pasan a T2, y 3 quedan en T1
            // Si son 4 grupos: floor(4/2) = 2 pasan a T2, y 2 quedan en T1
            $migratingCount = (int) floor($totalGroups / 2);
            $stayCount = $totalGroups - $migratingCount;

            $stayingGroups = $practiceGroups->slice(0, $stayCount)->values();
            $migratingGroups = $practiceGroups->slice($stayCount)->values();

            // 4. Renombrar correlativamente los grupos de Teoría 1 (P1A, P1B, P1C...)
            foreach ($stayingGroups as $i => $group) {
                $code = 'P1' . chr(65 + $i);
                if ($group->code !== $code) {
                    $group->update(['code' => $code]);
                }
            }

            // 5. Migrar y renombrar grupos a Teoría 2 reiniciando contador en 'A' (P2A, P2B...)
            $migratedStudents = 0;
            foreach ($migratingGroups as $i => $group) {
                $oldCode = $group->code;
                $newCode = 'P2' . chr(65 + $i);
                $oldTheoryId = $group->theory_group_id;

                $group->update([
                    'theory_group_id' => $theory2->id,
                    'code' => $newCode,
                ]);

                // Actualizar y auditar alumnos pertenecientes a estos grupos
                $enrollments = $group->enrollments()->get();
                foreach ($enrollments as $enrollment) {
                    $prevState = [
                        'theory_group_id' => $oldTheoryId,
                        'theory_name' => 'Teoría 1',
                        'practice_group_id' => $group->id,
                        'practice_code' => $oldCode,
                        'status' => $enrollment->status,
                    ];

                    $enrollment->update(['status' => 'reasignado']);

                    $newState = [
                        'theory_group_id' => $theory2->id,
                        'theory_name' => 'Teoría 2',
                        'practice_group_id' => $group->id,
                        'practice_code' => $newCode,
                        'status' => 'reasignado',
                    ];

                    AuditLog::create([
                        'batch_id' => $batchId,
                        'enrollment_id' => $enrollment->id,
                        'user_id' => $executor->id,
                        'action_type' => 'reallocation',
                        'previous_state' => $prevState,
                        'new_state' => $newState,
                        'description' => "División oficial: Migración de práctica {$oldCode} a {$newCode} en Teoría 2",
                        'is_reverted' => false,
                    ]);

                    $migratedStudents++;
                }
            }

            return [
                'success' => true,
                'message' => "Reorganización exitosa: Teoría dividida en T1 ({$stayCount} prácticas) y T2 ({$migratingCount} prácticas). Se migraron {$migratedStudents} estudiantes a Teoría 2.",
                'total_enrolled' => $totalEnrolled,
                'staying_groups_count' => $stayCount,
                'migrated_groups_count' => $migratingCount,
                'migrated_students' => $migratedStudents,
                'batch_id' => $batchId,
            ];
        });
    }

    /**
     * Operación manual del Delegado: Reasignación individual de un estudiante
     * Permite al delegado atender casos particulares, cruces de horarios o solicitudes directas.
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
            'description' => "Reasignación manual del delegado a grupo {$newGroup->code}",
            'is_reverted' => false,
        ]);

        return $enrollment;
    }

    /**
     * Operación manual del Delegado: Toggle Laptop
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
            'description' => 'Toggle laptop manual: ' . ($hasLaptop ? 'Sí' : 'No'),
            'is_reverted' => false,
        ]);

        return $enrollment;
    }

    /**
     * Operación manual del Delegado: Toggle Autorización Docente
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
            'description' => 'Toggle docente manual: ' . ($authorized ? 'Autorizado' : 'No Autorizado'),
            'is_reverted' => false,
        ]);

        return $enrollment;
    }

    /**
     * Alias de splitTheoryGroups para compatibilidad
     */
    public function splitTheoryAndGroups(Course $course, User $executor): array
    {
        return $this->splitTheoryGroups($course, $executor);
    }

    /**
     * Simulador Previo de División (Dry Run / Modo Preview)
     * Calcula la partición matemática truncada y el padrón de alumnos migrantes sin persistir cambios en la BD.
     */
    public function simulateSplit(Course $course): array
    {
        $totalEnrolled = $course->enrollments()->count();
        $theoriesCount = $course->theoryGroups()->count();

        if ($totalEnrolled < 60) {
            return [
                'can_split' => false,
                'message' => "No cumple condición: Se requieren al menos 60 alumnos matriculados (actualmente hay {$totalEnrolled}).",
                'total_enrolled' => $totalEnrolled,
                'theories_count' => $theoriesCount,
            ];
        }

        if ($theoriesCount >= 2) {
            return [
                'can_split' => false,
                'message' => "El curso ya cuenta con {$theoriesCount} teorías abiertas (Teoría 1 y Teoría 2 ya están operativas).",
                'total_enrolled' => $totalEnrolled,
                'theories_count' => $theoriesCount,
            ];
        }

        $theory1 = $course->theoryGroups()->where('name', 'like', '%Teoría 1%')->first()
            ?? $course->theoryGroups()->first();

        if (!$theory1) {
            return [
                'can_split' => false,
                'message' => "No se encontró grupo de Teoría 1 en esta asignatura.",
                'total_enrolled' => $totalEnrolled,
                'theories_count' => $theoriesCount,
            ];
        }

        $practiceGroups = PracticeGroup::where('theory_group_id', $theory1->id)
            ->with(['enrollments.user'])
            ->orderBy('code')
            ->get();

        $totalGroups = $practiceGroups->count();
        if ($totalGroups < 2) {
            return [
                'can_split' => false,
                'message' => "Se requieren al menos 2 grupos de práctica en Teoría 1 para realizar la partición.",
                'total_enrolled' => $totalEnrolled,
                'theories_count' => $theoriesCount,
            ];
        }

        $migratingCount = (int) floor($totalGroups / 2);
        $stayCount = $totalGroups - $migratingCount;

        $stayingGroups = $practiceGroups->slice(0, $stayCount)->values();
        $migratingGroups = $practiceGroups->slice($stayCount)->values();

        $t1Preview = [];
        $t1StudentsTotal = 0;
        foreach ($stayingGroups as $i => $group) {
            $newCode = 'P1' . chr(65 + $i);
            $count = $group->enrollments->count();
            $t1StudentsTotal += $count;
            $t1Preview[] = [
                'current_code' => $group->code,
                'new_code' => $newCode,
                'students_count' => $count,
            ];
        }

        $t2Preview = [];
        $t2StudentsTotal = 0;
        $migratingStudentsList = [];
        foreach ($migratingGroups as $i => $group) {
            $newCode = 'P2' . chr(65 + $i);
            $count = $group->enrollments->count();
            $t2StudentsTotal += $count;

            $studentsInGroup = [];
            foreach ($group->enrollments as $enr) {
                $user = $enr->user;
                $studentData = [
                    'name' => $user->name ?? 'Sin nombre',
                    'code' => $user->code ?? 'Sin código',
                    'has_laptop' => (bool)$enr->has_laptop,
                    'old_group' => $group->code,
                    'new_group' => $newCode,
                ];
                $studentsInGroup[] = $studentData;
                $migratingStudentsList[] = $studentData;
            }

            $t2Preview[] = [
                'current_code' => $group->code,
                'new_code' => $newCode,
                'students_count' => $count,
                'students' => $studentsInGroup,
            ];
        }

        return [
            'can_split' => true,
            'message' => "Simulación completada con éxito.",
            'total_enrolled' => $totalEnrolled,
            'total_groups' => $totalGroups,
            'stay_count' => $stayCount,
            'migrating_count' => $migratingCount,
            't1_preview' => $t1Preview,
            't1_students_total' => $t1StudentsTotal,
            't2_preview' => $t2Preview,
            't2_students_total' => $t2StudentsTotal,
            'migrating_students' => $migratingStudentsList,
        ];
    }
}
