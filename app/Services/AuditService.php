<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Enrollment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuditService
{
    /**
     * Registra un lote de reasignaciones masivas con un batch_id único.
     */
    public function logReallocationBatch(array $changes, ?int $userId = null, string $description = 'Reasignación masiva de grupo'): string
    {
        $batchId = (string) Str::uuid();

        foreach ($changes as $change) {
            AuditLog::create([
                'batch_id' => $batchId,
                'enrollment_id' => $change['enrollment_id'],
                'user_id' => $userId,
                'action_type' => 'REALLOCATION',
                'previous_state' => $change['previous_state'],
                'new_state' => $change['new_state'],
                'description' => $description,
                'is_reverted' => false,
            ]);
        }

        return $batchId;
    }

    /**
     * Revierte un lote completo de cambios utilizando su batch_id.
     */
    public function rollbackBatch(string $batchId): bool
    {
        return DB::transaction(function () use ($batchId) {
            $logs = AuditLog::where('batch_id', $batchId)
                ->where('is_reverted', false)
                ->get();

            if ($logs->isEmpty()) {
                return false;
            }

            foreach ($logs as $log) {
                $enrollment = Enrollment::find($log->enrollment_id);
                if ($enrollment && isset($log->previous_state['practice_group_id'])) {
                    // Restaurar el grupo de práctica y estado original
                    $enrollment->update([
                        'practice_group_id' => $log->previous_state['practice_group_id'],
                        'status' => $log->previous_state['status'] ?? 'original',
                    ]);
                }

                // Marcar el log como revertido
                $log->update(['is_reverted' => true]);
            }

            return true;
        });
    }
}