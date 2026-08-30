<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditService = new AuditService();
    }

    #[Test]
    public function crea_un_registro_de_auditoria_con_batch_id()
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->create();

        $changes = [
            [
                'enrollment_id' => $enrollment->id,
                'previous_state' => ['practice_group_id' => 1],
                'new_state' => ['practice_group_id' => 2],
            ]
        ];

        $batchId = $this->auditService->logReallocationBatch($changes, $user->id, 'Prueba de auditoría');

        $this->assertDatabaseHas('audit_logs', [
            'batch_id' => $batchId,
            'enrollment_id' => $enrollment->id,
            'action_type' => 'REALLOCATION',
            'is_reverted' => false,
        ]);
    }

    #[Test]
    public function revierte_un_lote_de_reasignacion_correctamente()
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->create();

        $log = AuditLog::create([
            'batch_id' => (string) \Illuminate\Support\Str::uuid(),
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'action_type' => 'REALLOCATION',
            'previous_state' => ['practice_group_id' => 1],
            'new_state' => ['practice_group_id' => 2],
            'description' => 'Lote para prueba de rollback',
            'is_reverted' => false,
        ]);

        $resultado = $this->auditService->rollbackBatch($log->batch_id);

        $this->assertTrue($resultado);
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'is_reverted' => true,
        ]);
    }
}