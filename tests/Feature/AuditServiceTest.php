<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuditService $auditService;
    protected Course $course;
    protected TheoryGroup $theoryGroup;
    protected PracticeGroup $group1;
    protected PracticeGroup $group2;
    protected User $user;
    protected Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditService = new AuditService();

        $this->user = User::create([
            'name' => 'Carlos Estudiante',
            'code' => '0202514001',
            'email' => 'carlos@uns.edu.pe',
            'password' => bcrypt('secret'),
            'role' => 'estudiante',
        ]);

        $this->course = Course::create([
            'code_course' => 'IF-301',
            'name' => 'Base de Datos I',
            'semester' => '2026-II',
        ]);

        $this->theoryGroup = TheoryGroup::create([
            'course_id' => $this->course->id,
            'name' => 'Teoría 1',
        ]);

        $this->group1 = PracticeGroup::create([
            'theory_group_id' => $this->theoryGroup->id,
            'code' => 'P1A',
            'base_capacity' => 15,
            'schedule' => 'Lunes 08:00-10:00',
        ]);

        $this->group2 = PracticeGroup::create([
            'theory_group_id' => $this->theoryGroup->id,
            'code' => 'P1B',
            'base_capacity' => 15,
            'schedule' => 'Lunes 10:00-12:00',
        ]);

        $this->enrollment = Enrollment::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'practice_group_id' => $this->group1->id,
            'status' => 'original',
            'enrolled_at' => now(),
            'has_laptop' => false,
            'teacher_authorized' => false,
        ]);
    }

    #[Test]
    public function crea_un_registro_de_auditoria_con_batch_id()
    {
        $changes = [
            [
                'enrollment_id' => $this->enrollment->id,
                'previous_state' => ['practice_group_id' => $this->group1->id],
                'new_state' => ['practice_group_id' => $this->group2->id],
            ]
        ];

        $batchId = $this->auditService->logReallocationBatch($changes, $this->user->id, 'Prueba de auditoría');

        $this->assertDatabaseHas('audit_logs', [
            'batch_id' => $batchId,
            'enrollment_id' => $this->enrollment->id,
            'action_type' => 'REALLOCATION',
            'is_reverted' => false,
        ]);
    }

    #[Test]
    public function revierte_un_lote_de_reasignacion_correctamente()
    {
        // Simulamos que el estudiante fue movido al grupo 2
        $this->enrollment->update(['practice_group_id' => $this->group2->id, 'status' => 'reasignado']);

        $batchId = (string) \Illuminate\Support\Str::uuid();
        $log = AuditLog::create([
            'batch_id' => $batchId,
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->user->id,
            'action_type' => 'REALLOCATION',
            'previous_state' => ['practice_group_id' => $this->group1->id, 'status' => 'original'],
            'new_state' => ['practice_group_id' => $this->group2->id, 'status' => 'reasignado'],
            'description' => 'Lote para prueba de rollback',
            'is_reverted' => false,
        ]);

        $resultado = $this->auditService->rollbackBatch($batchId);

        $this->assertTrue($resultado);
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'is_reverted' => true,
        ]);

        // Verificar que el estudiante regresó al grupo 1 y estado original
        $this->enrollment->refresh();
        $this->assertEquals($this->group1->id, $this->enrollment->practice_group_id);
        $this->assertEquals('original', $this->enrollment->status);
    }

    #[Test]
    public function revierte_una_accion_individual_correctamente()
    {
        // Simulamos un toggle manual de laptop
        $this->enrollment->update(['has_laptop' => true]);

        $log = $this->auditService->logAction(
            $this->enrollment,
            'laptop_toggle',
            ['has_laptop' => false],
            ['has_laptop' => true],
            $this->user->id,
            null,
            'Toggle de laptop de prueba'
        );

        $resultado = $this->auditService->rollbackSingle($log);

        $this->assertTrue($resultado);
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'is_reverted' => true,
        ]);

        $this->enrollment->refresh();
        $this->assertFalse($this->enrollment->has_laptop);
    }
}