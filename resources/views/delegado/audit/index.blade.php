@extends('layouts.app')

@section('title', 'Registro de Auditoría')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bitácora de Auditoría</h1>
            <p class="text-xs sm:text-sm text-slate-500">Historial transaccional con trazabilidad y reversión de cambios.</p>
        </div>
        <div>
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="ph ph-arrow-u-up-left text-lg"></i> Deshacer Último Lote Masivo
            </button>
        </div>
    </div>

    <!-- Filtros de Auditoría -->
    <form method="GET" action="{{ route('audit.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Buscar en descripción</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: P1C, reasignación..." class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo de Acción</label>
            <select name="action_type" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600 focus:outline-none">
                <option value="">Todas las acciones</option>
                <option value="reallocation" {{ request('action_type') == 'reallocation' ? 'selected' : '' }}>Reasignación</option>
                <option value="laptop_toggle" {{ request('action_type') == 'laptop_toggle' ? 'selected' : '' }}>Cambio Laptop</option>
                <option value="auth_toggle" {{ request('action_type') == 'auth_toggle' ? 'selected' : '' }}>Permiso Docente</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 bg-slate-900 hover:bg-black text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2">
                <i class="ph ph-funnel"></i> Filtrar Logs
            </button>
            @if(request()->hasAny(['search', 'action_type']))
                <a href="{{ route('audit.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition" title="Limpiar">
                    <i class="ph ph-x text-lg"></i>
                </a>
            @endif
        </div>
    </form>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">ID / Batch</th>
                        <th class="px-5 py-3.5">Responsable</th>
                        <th class="px-5 py-3.5">Tipo de Acción</th>
                        <th class="px-5 py-3.5">Descripción del Cambio</th>
                        <th class="px-5 py-3.5">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-normal">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-400">#{{ substr($log->batch_id ?? $log->id, 0, 8) }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $log->user->name ?? 'Sistema' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-md border border-blue-200">
                                    {{ ucfirst($log->action_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-800">{{ $log->description }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 font-mono">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                <i class="ph ph-clock-counter-clockwise text-3xl mb-1 block"></i>
                                No hay registros de auditoría que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection