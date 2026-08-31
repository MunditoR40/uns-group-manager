@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bitácora de Auditoría y Reversiones (Rollback)</h1>
            <p class="text-sm text-gray-500">Historial inmutable de movimientos, reasignaciones masivas y ajustes manuales.</p>
        </div>
    </div>

    <!-- Mensajes de Alerta -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-300 text-green-800 rounded-lg text-sm flex items-center">
            <span class="font-bold mr-2">✓ Éxito:</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-300 text-red-800 rounded-lg text-sm flex items-center">
            <span class="font-bold mr-2">✗ Error:</span> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote (Batch)</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estudiante</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción / Detalle</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-500 font-mono">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-gray-600">
                                {{ $log->batch_id ? Str::limit($log->batch_id, 8) : 'Manual' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-800 font-medium">
                                {{ $log->enrollment?->user?->name ?? 'N/A' }}
                                <div class="text-xs text-gray-400 font-mono">{{ $log->enrollment?->user?->code ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-700">
                                <span class="font-bold text-gray-900 uppercase tracking-wider">{{ $log->action_type }}</span>
                                @if($log->description)
                                    <p class="text-gray-500 text-xs mt-0.5">{{ $log->description }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                @if($log->is_reverted)
                                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Revertido
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs font-medium">
                                @if(!$log->is_reverted)
                                    @if($log->batch_id)
                                        <form action="{{ route('audit.rollback', $log->batch_id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('¿Estás seguro de deshacer los cambios de todo este lote ({{ Str::limit($log->batch_id, 8) }})?')"
                                                    class="text-red-600 hover:text-red-900 font-semibold bg-red-50 hover:bg-red-100 py-1 px-2.5 rounded border border-red-200 transition">
                                                Revertir Lote
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('audit.rollback-single', $log) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('¿Estás seguro de deshacer esta acción individual?')"
                                                    class="text-amber-700 hover:text-amber-900 font-semibold bg-amber-50 hover:bg-amber-100 py-1 px-2.5 rounded border border-amber-200 transition">
                                                Revertir Acción
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-gray-400">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                                No hay registros de auditoría aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection