<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    // Muestra la bitácora de auditoría agrupada o paginada
    public function index()
    {
        $logs = AuditLog::with(['enrollment.user', 'user'])
            ->latest()
            ->paginate(15);

        return view('audit.index', compact('logs'));
    }

    // Ejecuta la reversión de un lote específico por batch_id
    public function rollback(Request $request, string $batchId)
    {
        $success = $this->auditService->rollbackBatch($batchId);

        if ($success) {
            return redirect()->back()->with('success', 'Reasignación revertida exitosamente.');
        }

        return redirect()->back()->with('error', 'No se pudo revertir el lote o ya fue revertido previamente.');
    }

    // Ejecuta la reversión de una acción individual
    public function rollbackSingle(Request $request, AuditLog $auditLog)
    {
        $success = $this->auditService->rollbackSingle($auditLog);

        if ($success) {
            return redirect()->back()->with('success', 'Acción de auditoría revertida exitosamente.');
        }

        return redirect()->back()->with('error', 'No se pudo revertir la acción o ya fue revertida previamente.');
    }
}