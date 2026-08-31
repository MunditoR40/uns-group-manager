@extends('layouts.app')

@section('title', 'Alumnos Matriculados')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Matrículas y Reasignación</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Curso: <strong class="text-slate-800">{{ $course->name ?? 'Sin Curso' }}</strong> ({{ $course->code_course ?? '---' }}) — Semestre {{ $course->semester ?? '2026-II' }}
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('enrollments.index') }}" class="flex items-center">
                <select name="course_id" onchange="this.form.submit()" class="px-3 py-2 text-xs sm:text-sm font-semibold bg-white border border-slate-300 rounded-xl shadow-sm focus:ring-2 focus:ring-red-600 focus:outline-none">
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ $course && $course->id == $c->id ? 'selected' : '' }}>
                            {{ $c->code_course }} - {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('export.excel', ['course_id' => $course?->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs sm:text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="ph ph-file-xls text-emerald-600 text-lg"></i> Excel (.xlsx)
            </a>
            <a href="{{ route('export.pdf', ['course_id' => $course?->id]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs sm:text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="ph ph-file-pdf text-rose-600 text-lg"></i> PDF
            </a>
            <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-800 hover:bg-red-900 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="ph ph-lightning text-lg"></i> Reasignación FIFO
            </button>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('enrollments.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <input type="hidden" name="course_id" value="{{ $course?->id }}">

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Buscar por Nombre o Código</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: 0202114... o Perez" class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600 focus:outline-none transition">
                <i class="ph ph-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Grupo de Práctica</label>
            <select name="practice_group" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600 focus:outline-none transition">
                <option value="">Todos los grupos ({{ $groups->count() }})</option>
                @foreach($groups as $grp)
                    <option value="{{ $grp->code }}" {{ request('practice_group') == $grp->code ? 'selected' : '' }}>
                        {{ $grp->code }} ({{ $grp->theoryGroup->name }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Criterio de Orden</label>
            <select name="sort" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600 focus:outline-none transition">
                <option value="fifo" {{ request('sort') == 'fifo' ? 'selected' : '' }}>Fecha de Matrícula (FIFO)</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Alfabético (A - Z)</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Alfabético (Z - A)</option>
                <option value="code_asc" {{ request('sort') == 'code_asc' ? 'selected' : '' }}>Código Universitario</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 bg-slate-900 hover:bg-black text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2">
                <i class="ph ph-funnel"></i> Filtrar
            </button>
            @if(request()->hasAny(['search', 'practice_group', 'sort']))
                <a href="{{ route('enrollments.index', ['course_id' => $course?->id]) }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition" title="Limpiar Filtros">
                    <i class="ph ph-x text-lg"></i>
                </a>
            @endif
        </div>
    </form>

    <!-- Tabla Dinámica -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50/80 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-200 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Código UNS</th>
                        <th class="px-5 py-3.5">Estudiante</th>
                        <th class="px-5 py-3.5">Grupo y Teoría</th>
                        <th class="px-5 py-3.5">Fecha y Hora Matrícula</th>
                        <th class="px-5 py-3.5 text-center">Tiene Laptop</th>
                        <th class="px-5 py-3.5 text-center">Permiso Docente</th>
                        <th class="px-5 py-3.5 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-normal">
                    @forelse($enrollments as $enrollment)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5 font-mono font-semibold text-slate-900">{{ $enrollment->user->code ?? 'S/C' }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $enrollment->user->name }}</td>
                            <td class="px-5 py-3.5">
                                @php
                                    $theoryName = $enrollment->practiceGroup->theoryGroup->name ?? 'Teoría 1';
                                    $badgeType = str_contains($theoryName, '1') ? 't1' : 't2';
                                @endphp
                                <x-badge :type="$badgeType">
                                    {{ $enrollment->practiceGroup->code }} • {{ $theoryName }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 font-mono">
                                {{ \Carbon\Carbon::parse($enrollment->enrolled_at)->format('d/m/Y H:i:s') }}
                            </td>
                            <!-- Toggle Laptop con Confirmación -->
                            <td class="px-5 py-3.5 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           {{ $enrollment->has_laptop ? 'checked' : '' }} 
                                           onchange="solicitarConfirmacion(this, {{ $enrollment->id }}, 'has_laptop', '{{ addslashes($enrollment->user->name) }}')"
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-700"></div>
                                </label>
                            </td>
                            <!-- Toggle Permiso Docente con Confirmación -->
                            <td class="px-5 py-3.5 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           {{ $enrollment->teacher_authorized ? 'checked' : '' }} 
                                           onchange="solicitarConfirmacion(this, {{ $enrollment->id }}, 'teacher_authorized', '{{ addslashes($enrollment->user->name) }}')"
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-700"></div>
                                </label>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <x-badge :type="$enrollment->status === 'reasignado' ? 'reassigned' : 'success'">
                                    {{ ucfirst($enrollment->status) }}
                                </x-badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No hay estudiantes matriculados en este curso con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enrollments->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function solicitarConfirmacion(checkbox, enrollmentId, field, studentName) {
        const nuevoEstado = checkbox.checked;
        const nombreCampo = field === 'has_laptop' ? 'Uso de Laptop' : 'Autorización Docente';
        const accion = nuevoEstado ? 'habilitar' : 'deshabilitar';

        Swal.fire({
            title: `¿Confirmar cambio para ${studentName}?`,
            text: `Se va a ${accion} el parámetro "${nombreCampo}".`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#800000',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, guardar cambio',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'rounded-3xl shadow-xl',
                confirmButton: 'rounded-xl font-bold px-4 py-2',
                cancelButton: 'rounded-xl font-medium px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/enrollments/${enrollmentId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ field: field, value: nuevoEstado })
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok && data.success) {
                        Swal.fire({
                            title: '¡Guardado!',
                            text: 'El estado se actualizó correctamente.',
                            icon: 'success',
                            timer: 1400,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-3xl' }
                        });
                    } else {
                        throw new Error(data.message || 'Error al guardar');
                    }
                })
                .catch(err => {
                    checkbox.checked = !nuevoEstado;
                    Swal.fire({
                        title: 'Error',
                        text: err.message || 'No se pudo actualizar el registro.',
                        icon: 'error',
                        confirmButtonColor: '#800000'
                    });
                });
            } else {
                checkbox.checked = !nuevoEstado;
            }
        });
    }
</script>
@endpush