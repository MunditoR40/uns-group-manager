@extends('layouts.app')

@section('title', $course->name)

@section('content')
<div class="space-y-8" x-data="{ modalDivision: false, modalReasignar: false, selectedEnrollment: null, studentName: '', currentGroup: '' }">

    <!-- Navegación Superior y Selector Rápido de Curso -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('courses.index') }}" class="hover:text-red-700 transition">Cursos</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">{{ $course->code_course }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">{{ $course->name }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Código: <span class="font-bold text-slate-700">{{ $course->code_course }}</span> • 
                Ciclo: <span class="font-bold text-slate-700">{{ $course->cycle ?? 'II Ciclo' }}</span> • 
                Semestre: <span class="font-bold text-slate-700">{{ $course->semester }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <select onchange="window.location.href='/courses/' + this.value" 
                        class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-red-600">
                    <option value="">Cambiar asignatura...</option>
                    @foreach($allCourses as $ac)
                        <option value="{{ $ac->id }}" {{ $ac->id === $course->id ? 'selected' : '' }}>
                            {{ $ac->code_course }} - {{ $ac->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(Route::has('exports.enrollments.excel'))
                <a href="{{ route('exports.enrollments.excel') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="ph ph-file-xls text-base"></i>
                    <span>Exportar Excel</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Banner Informativo de Aforos / Alerta de Sobrecupo -->
    @php
        $gruposSobrecupo = $practiceGroups->filter(function($g) {
            $capacidadEfectiva = ($g->base_capacity ?? 15) + $g->justified_count;
            return $g->enrollments_count > $capacidadEfectiva;
        });

        $theoriesCount = $course->theoryGroups->count();
        $isSplittable = ($totalEnrolled >= 60 && $theoriesCount < 2);
    @endphp

    @if($gruposSobrecupo->count() > 0)
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50/90 text-rose-900 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="ph ph-warning-diamond"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Sobrecupo Crítico en Laboratorios</h4>
                    <p class="text-xs text-rose-800 mt-0.5">
                        Hay {{ $gruposSobrecupo->count() }} laboratorios que superan los límites incluso considerando alumnos con laptop y permisos docentes.
                    </p>
                </div>
            </div>
            @if($isSplittable)
                <button @click="modalDivision = true" 
                        class="px-4 py-2 bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
                    Reorganizar Teorías (T1 → T2)
                </button>
            @endif
        </div>
    @else
        <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/90 text-emerald-900 shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="ph ph-check-circle"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Aforos Controlados y Balanceados</h4>
                    <p class="text-xs text-emerald-800 mt-0.5">
                        Los estudiantes cuentan con puesto asignado en equipo físico o modalidad laptop/autorización docente.
                    </p>
                </div>
            </div>
            @if($isSplittable)
                <button @click="modalDivision = true" 
                        class="px-4 py-2 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
                    Habilitar Teoría 2 (≥60 Alumnos)
                </button>
            @endif
        </div>
    @endif

    <!-- Tarjetas de Métricas del Curso -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-users"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Matriculados</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalEnrolled }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-laptop"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Con Laptop</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalLaptops }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-certificate"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Autorizados por Docente</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalAuthorized }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-arrows-clockwise"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Alumnos Reasignados</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalReassigned }}</h3>
            </div>
        </div>
    </div>

    <!-- TARJETAS DE AFORO POR GRUPO DE PRÁCTICA -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-slate-900">Estado de Aforos por Laboratorio</h2>
            <span class="text-xs text-slate-500 font-medium">Aforo Base vs Aforo Efectivo (con justificados)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($practiceGroups as $group)
                @php
                    $currentCount = $group->enrollments_count;
                    $baseCap = $group->base_capacity ?? 15;
                    $effectiveCap = $baseCap + $group->justified_count;
                    $percentage = $effectiveCap > 0 ? min(round(($currentCount / $effectiveCap) * 100), 100) : 0;

                    $barColor = 'bg-blue-600';
                    if ($currentCount > $effectiveCap) {
                        $barColor = 'bg-red-600';
                    } elseif ($currentCount >= $baseCap) {
                        $barColor = 'bg-amber-500';
                    }

                    $isSelected = request('practice_group_id') == $group->id;
                    $targetUrl = $isSelected 
                        ? route('courses.show', array_merge(['course' => $course], request()->except(['practice_group_id', 'page'])))
                        : route('courses.show', array_merge(['course' => $course], request()->except('page'), ['practice_group_id' => $group->id]));
                @endphp
                <div onclick="window.location.href='{{ $targetUrl }}'"
                     title="{{ $isSelected ? 'Clic para quitar filtro de ' . $group->code : 'Clic para filtrar estudiantes de ' . $group->code }}"
                     class="rounded-2xl p-5 flex flex-col justify-between cursor-pointer transition-all duration-200 group relative {{ $isSelected ? 'bg-red-50/40 border-2 border-red-800 ring-2 ring-red-800/30 shadow-md -translate-y-1' : 'bg-white border border-slate-200 shadow-sm hover:border-red-400 hover:shadow-lg hover:-translate-y-1' }}">
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900 text-lg group-hover:text-red-800 transition">{{ $group->code }}</span>
                                @if($isSelected)
                                    <span class="text-[10px] font-extrabold bg-red-800 text-white px-2 py-0.5 rounded-full flex items-center gap-1 shadow-sm">
                                        <i class="ph ph-funnel"></i> Activo
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ str_contains($group->theoryGroup->name ?? '', '2') ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $group->theoryGroup->name ?? 'Teoría 1' }}
                            </span>
                        </div>

                        <div class="flex justify-between text-xs text-slate-600 mb-1">
                            <span>Matriculados:</span>
                            <span class="font-extrabold {{ $currentCount > $effectiveCap ? 'text-red-600' : 'text-slate-800' }}">
                                {{ $currentCount }} / {{ $baseCap }}
                            </span>
                        </div>

                        <!-- Barra de Progreso -->
                        <div class="w-full bg-slate-100 rounded-full h-2.5 mb-2 overflow-hidden">
                            <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                        </div>

                        <div class="space-y-1 text-[11px] text-slate-500 border-t border-slate-100 pt-2 mb-3">
                            <div class="flex justify-between">
                                <span>Laptops: <strong class="text-slate-700">{{ $group->laptop_count }}</strong></span>
                                <span>Permisos: <strong class="text-slate-700">{{ $group->teacher_auth_count }}</strong></span>
                            </div>
                            <div class="text-right font-medium text-slate-400">
                                Aforo efectivo: <span class="font-bold text-slate-700">{{ $effectiveCap }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        @if(Route::has('exports.practice-groups.pdf'))
                            <a href="{{ route('exports.practice-groups.pdf', $group) }}" 
                               onclick="event.stopPropagation();"
                               title="Descargar Acta en PDF"
                               class="w-full text-center py-1.5 px-3 text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 rounded-xl border border-red-200 transition flex items-center justify-center gap-1.5 shadow-sm">
                                <i class="ph ph-file-pdf text-base"></i>
                                <span>Acta Oficial PDF</span>
                            </a>
                        @endif

                        <div class="text-[10px] font-semibold text-center {{ $isSelected ? 'text-red-800 font-bold' : 'text-slate-400 group-hover:text-red-700' }} transition flex items-center justify-center gap-1">
                            <i class="ph {{ $isSelected ? 'ph-x-circle' : 'ph-funnel-simple' }}"></i>
                            <span>{{ $isSelected ? 'Clic para quitar filtro' : 'Clic para filtrar grupo' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- BARRA DE FILTROS Y BÚSQUEDA -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="{{ route('courses.show', $course) }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label for="search" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Estudiante</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Nombre o código..." 
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-red-600 focus:ring-1 focus:ring-red-600">
            </div>

            <div>
                <label for="theory_group_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Teoría</label>
                <select name="theory_group_id" id="theory_group_id" 
                        class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    <option value="">Todas las teorías</option>
                    @foreach($theoryGroups as $tg)
                        <option value="{{ $tg->id }}" {{ request('theory_group_id') == $tg->id ? 'selected' : '' }}>
                            {{ $tg->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="practice_group_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Grupo Práctica</label>
                <select name="practice_group_id" id="practice_group_id" 
                        class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    <option value="">Todos los grupos</option>
                    @foreach($practiceGroups as $pg)
                        <option value="{{ $pg->id }}" {{ request('practice_group_id') == $pg->id ? 'selected' : '' }}>
                            {{ $pg->code }} ({{ $pg->theoryGroup->name ?? 'Teoría' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sort" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Orden</label>
                <select name="sort" id="sort" 
                        class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                    <option value="fifo" {{ request('sort') == 'fifo' ? 'selected' : '' }}>FIFO (Inscripción)</option>
                    <option value="alphabetical_asc" {{ request('sort') == 'alphabetical_asc' ? 'selected' : '' }}>Alfabético (A-Z)</option>
                    <option value="alphabetical_desc" {{ request('sort') == 'alphabetical_desc' ? 'selected' : '' }}>Alfabético (Z-A)</option>
                    <option value="code" {{ request('sort') == 'code' ? 'selected' : '' }}>Código Universitario</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-red-800 hover:bg-red-900 text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition">
                    Filtrar
                </button>
                <a href="{{ route('courses.show', $course) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2.5 rounded-xl font-semibold text-xs transition border border-slate-200">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- TABLA INTERACTIVA DE ESTUDIANTES -->
    <div id="tabla-estudiantes" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        @if(request('practice_group_id'))
            @php
                $filteredGroup = $practiceGroups->firstWhere('id', request('practice_group_id'));
            @endphp
            @if($filteredGroup)
                <div class="px-6 py-3 bg-red-50/80 border-b border-red-100 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs text-red-950 font-medium">
                        <i class="ph ph-funnel text-base text-red-700"></i>
                        <span>Filtrando por el grupo de práctica: <strong class="font-bold text-red-800">{{ $filteredGroup->code }}</strong> ({{ $filteredGroup->theoryGroup->name ?? 'Teoría' }})</span>
                    </div>
                    <a href="{{ route('courses.show', array_merge(['course' => $course], request()->except(['practice_group_id', 'page']))) }}" 
                       class="text-xs font-bold text-red-700 hover:text-red-900 flex items-center gap-1 hover:underline">
                        <i class="ph ph-x-circle text-sm"></i> Quitar filtro
                    </a>
                </div>
            @endif
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Código</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Estudiante</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Grupo Práctica</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Inscripción FIFO</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Laptop</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Permiso</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($enrollments as $e)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-800">
                                {{ $e->user->code ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm font-semibold text-slate-900">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span>{{ $e->user->name ?? 'Sin nombre' }}</span>
                                    @if(optional($e->user)->role === 'delegado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shadow-sm" title="Delegado Oficial de Sección">
                                            <i class="ph ph-crown-simple text-amber-700 font-bold text-xs"></i> Delegado
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $tName = $e->practiceGroup->theoryGroup->name ?? 'Teoría 1';
                                    $badgeType = str_contains($tName, '2') ? 't2' : 't1';
                                @endphp
                                <x-badge :type="$badgeType">
                                    {{ $e->practiceGroup->code ?? '---' }} • {{ $tName }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 font-mono">
                                {{ $e->enrolled_at ? \Carbon\Carbon::parse($e->enrolled_at)->format('d/m/Y H:i:s') : 'N/A' }}
                            </td>
                            
                            <!-- Toggle Switch Laptop con Confirmación SweetAlert2 y AJAX -->
                            <td class="px-5 py-3.5 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           {{ $e->has_laptop ? 'checked' : '' }} 
                                           onchange="solicitarConfirmacionToggle(this, {{ $e->id }}, 'has_laptop', '{{ addslashes($e->user->name) }}')"
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-800"></div>
                                </label>
                            </td>

                            <!-- Toggle Switch Permiso Docente con Confirmación SweetAlert2 y AJAX -->
                            <td class="px-5 py-3.5 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           {{ $e->teacher_authorized ? 'checked' : '' }} 
                                           onchange="solicitarConfirmacionToggle(this, {{ $e->id }}, 'teacher_authorized', '{{ addslashes($e->user->name) }}')"
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-800"></div>
                                </label>
                            </td>

                            <!-- Estado -->
                            <td class="px-5 py-3.5 text-center">
                                <x-badge :type="$e->status === 'reasignado' ? 'reassigned' : 'success'">
                                    {{ ucfirst($e->status) }}
                                </x-badge>
                            </td>

                            <!-- Acciones del Delegado -->
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" 
                                            @click="modalReasignar = true; selectedEnrollment = {{ $e->id }}; studentName = '{{ addslashes($e->user->name) }}'; currentGroup = '{{ $e->practiceGroup->code ?? '' }}'"
                                            title="Reasignar a otro grupo de práctica"
                                            class="px-2.5 py-1 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-red-50 hover:text-red-700 rounded-lg border border-slate-200 transition">
                                        Mover
                                    </button>

                                    @if($e->user)
                                        <form method="POST" action="{{ route('students.toggle-delegate', $e->user->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    title="{{ $e->user->role === 'delegado' ? 'Quitar designación de delegado' : 'Designar como Delegado Oficial' }}"
                                                    class="px-2 py-1 text-xs font-bold rounded-lg border transition {{ $e->user->role === 'delegado' ? 'bg-amber-100 text-amber-800 border-amber-300 hover:bg-amber-200' : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200' }}">
                                                <i class="ph ph-crown-simple text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400">
                                No se encontraron estudiantes con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enrollments->hasPages())
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: Confirmación de Reorganización y División T1 -> T2 -->
    <div x-show="modalDivision" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-5" @click.away="modalDivision = false">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-700 flex items-center justify-center text-2xl font-bold">
                    <i class="ph ph-git-fork"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">Reorganización y División de Teorías</h3>
                    <p class="text-xs text-slate-500">Reglamento Académico Oficial UNS (≥ 60 alumnos)</p>
                </div>
            </div>

            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-xs text-slate-600 space-y-2">
                <p><strong>Curso:</strong> {{ $course->name }} ({{ $course->code_course }})</p>
                <p><strong>Matriculados actuales:</strong> {{ $totalEnrolled }} estudiantes.</p>
                <p><strong>Regla de partición truncada:</strong> Se conservará (N - ⌊N/2⌋) grupos en Teoría 1 y se moverá ⌊N/2⌋ grupos a la nueva <strong>Teoría 2</strong> reiniciando correlativo en P2A.</p>
                <p class="text-red-700 font-semibold">Todos los movimientos se registrarán en la bitácora inmutable con Batch ID para posibilidad de Rollback.</p>
            </div>

            <form method="POST" action="{{ route('courses.reallocate', $course) }}">
                @csrf
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="modalDivision = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-xs font-bold shadow-sm transition">
                        Confirmar y Ejecutar División
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Reasignación Manual Individual de Alumno -->
    <div x-show="modalReasignar" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-8 space-y-5" @click.away="modalReasignar = false">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl font-bold">
                    <i class="ph ph-user-switch"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">Reasignar Grupo Manualmente</h3>
                    <p class="text-xs text-slate-500">Atención de cruces de horario o peticiones</p>
                </div>
            </div>

            <div class="text-xs text-slate-600 space-y-1">
                <p>Estudiante: <strong class="text-slate-900" x-text="studentName"></strong></p>
                <p>Grupo actual: <span class="px-2 py-0.5 rounded bg-slate-100 font-bold" x-text="currentGroup"></span></p>
            </div>

            <form :action="'/enrollments/' + selectedEnrollment + '/move-group'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nuevo Grupo de Práctica</label>
                    <select name="new_practice_group_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-red-600 focus:ring-1 focus:ring-red-600">
                        @foreach($practiceGroups as $pg)
                            <option value="{{ $pg->id }}">
                                {{ $pg->code }} ({{ $pg->theoryGroup->name ?? 'Teoría' }}) - Horario: {{ $pg->schedule ?? 'Por definir' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="modalReasignar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-xs font-bold shadow-sm transition">
                        Guardar Cambio
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function solicitarConfirmacionToggle(checkbox, enrollmentId, field, studentName) {
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
                popup: 'rounded-3xl shadow-2xl',
                confirmButton: 'rounded-xl font-bold px-4 py-2 text-xs',
                cancelButton: 'rounded-xl font-medium px-4 py-2 text-xs'
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
                            text: data.message,
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

    // Preservar la posición exacta del scroll al filtrar para evitar saltos de pantalla
    window.addEventListener('beforeunload', () => {
        sessionStorage.setItem('courses_scroll_pos_' + window.location.pathname, window.scrollY);
    });

    document.addEventListener('DOMContentLoaded', () => {
        const savedScroll = sessionStorage.getItem('courses_scroll_pos_' + window.location.pathname);
        if (savedScroll !== null) {
            window.scrollTo({
                top: parseInt(savedScroll, 10),
                behavior: 'instant'
            });
            sessionStorage.removeItem('courses_scroll_pos_' + window.location.pathname);
        }
    });
</script>
@endpush
