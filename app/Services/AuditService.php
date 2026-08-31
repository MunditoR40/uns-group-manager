<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuditService
{
    /**
     * Registra una acción individual de auditoría.
     */
    public function logAction(
        Enrollment $enrollment,
        string $actionType,
        array $previousState,
        array $newState,
        ?int $userId = null,
        ?string $batchId = null,
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'batch_id' => $batchId,
            'enrollment_id' => $enrollment->id,
            'user_id' => $userId,
            'action_type' => $actionType,
            'previous_state' => $previousState,
            'new_state' => $newState,
            'description' => $description,
            'is_reverted' => false,
        ]);
    }

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
    public function rollbackBatch(string $batchId, ?User $executor = null): bool
    {
        return DB::transaction(function () use ($batchId) {
            $logs = AuditLog::where('batch_id', $batchId)
                ->where('is_reverted', false)
                ->get();

            if ($logs->isEmpty()) {
                return false;
            }

            foreach ($logs as $log) {
                $this->revertSingleLog($log);
            }

            return true;
        });
    }

    /**
     * Revierte una acción puntual de auditoría.
     */
    public function rollbackSingle(AuditLog $auditLog, ?User $executor = null): bool
    {
        if ($auditLog->is_reverted) {
            return false;
        }

        return DB::transaction(function () use ($auditLog) {
            return $this->revertSingleLog($auditLog);
        });
    }

    /**
     * Helper para restaurar el estado previo de un log específico.
     */
    protected function revertSingleLog(AuditLog $log): bool
    {
        $enrollment = Enrollment::find($log->enrollment_id);
        $prevState = $log->previous_state ?? [];

        if ($enrollment && is_array($prevState)) {
            $updateData = [];

            if (array_key_exists('practice_group_id', $prevState)) {
                $updateData['practice_group_id'] = $prevState['practice_group_id'];
            }
            if (array_key_exists('status', $prevState)) {
                $updateData['status'] = $prevState['status'];
            }
            if (array_key_exists('has_laptop', $prevState)) {
                $updateData['has_laptop'] = $prevState['has_laptop'];
            }
            if (array_key_exists('teacher_authorized', $prevState)) {
                $updateData['teacher_authorized'] = $prevState['teacher_authorized'];
            }

            if (!empty($updateData)) {
                $enrollment->update($updateData);
            }
        }

        // Si el cambio involucraba grupo de práctica (cambio de teoría o código)
        if (isset($prevState['practice_group_id']) && (isset($prevState['theory_group_id']) || isset($prevState['practice_code']))) {
            $group = PracticeGroup::find($prevState['practice_group_id']);
            if ($group) {
                $groupUpdates = [];
                if (isset($prevState['theory_group_id'])) {
                    $groupUpdates['theory_group_id'] = $prevState['theory_group_id'];
                }
                if (isset($prevState['practice_code'])) {
                    $groupUpdates['code'] = $prevState['practice_code'];
                }
                if (!empty($groupUpdates)) {
                    $group->update($groupUpdates);
                }
            }
        }

        // Marcar el log como revertido
        $log->update(['is_reverted' => true]);

        return true;
    }
}