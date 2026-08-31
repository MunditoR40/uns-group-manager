@extends('layouts.app')

@section('title', 'Panel Principal')

@section('content')
<div class="space-y-8">
    <!-- Header Hero -->
    <div class="bg-gradient-to-r from-red-900 via-red-800 to-red-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-red-100 border border-white/10">
                Semestre Académico {{ $selectedCourse->semester ?? '2026-II' }}
            </span>
            <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-3 leading-tight">Gestión de Grupos y Horarios</h1>
            <p class="text-red-100 text-sm sm:text-base mt-2 font-normal">
                Supervisa el balance de laboratorios, aforos efectivos por laptop/permisos y reasignaciones.
            </p>
        </div>
        <div class="absolute -right-8 -bottom-12 opacity-10 text-white pointer-events-none">
            <i class="ph ph-buildings text-[220px]"></i>
        </div>
    </div>

    <!-- Panel de Validación y Alertas de Aforo -->
    @php
        $gruposConSobrecupoCritico = $practiceGroups->filter(function($g) {
            $capacidadEfectiva = 15 + $g->justified_count;
            return $g->enrollments_count > $capacidadEfectiva;
        });
    @endphp

    @if($gruposConSobrecupoCritico->count() > 0)
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50/80 text-rose-900 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="ph ph-warning-diamond"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold">Sobrecupo Crítico Detectado</h4>
                    <p class="text-xs text-rose-800 mt-0.5">
                        Hay {{ $gruposConSobrecupoCritico->count() }} laboratorios que superan los límites incluso considerando laptops y permisos docentes.
                    </p>
                </div>
            </div>
            @if($selectedCourse)
            <a href="{{ route('enrollments.index', ['course_id' => $selectedCourse->id]) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition text-center whitespace-nowrap">
                Revisar Alumnos
            </a>
            @endif
        </div>
    @else
        <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/80 text-emerald-900 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="ph ph-check-circle"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold">Aforos Cubiertos y Balanceados</h4>
                <p class="text-xs text-emerald-800 mt-0.5">Todos los estudiantes cuentan con puesto asignado en PC base, laptop o autorización docente.</p>
            </div>
        </div>
    @endif

    <!-- Tarjetas de Métricas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-users"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Matriculados en Curso</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $totalEnrolled }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-laptop"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Con Laptop Habilitada</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $laptopCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-arrows-left-right"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Reasignados</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $reassignedCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-chalkboard-teacher"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Cursos</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $courses->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- Lista de Cursos -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="ph ph-books text-red-700 text-xl"></i> Cursos Registrados ({{ $courses->count() }})
            </h2>
            <span class="text-xs text-slate-500">Selecciona un curso para ver sus métricas</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($courses as $c)
                @php $isSelected = $selectedCourse && $selectedCourse->id === $c->id; @endphp
                <a href="{{ route('dashboard', ['course_id' => $c->id]) }}" 
                   class="p-5 rounded-2xl border transition text-left flex flex-col justify-between block {{ $isSelected ? 'border-red-600 bg-red-50/40 shadow-md ring-2 ring-red-600/20' : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm' }}">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded {{ $isSelected ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700' }}">
                                {{ $c->code_course }}
                            </span>
                            @if($isSelected)
                                <span class="text-xs font-bold text-red-700 flex items-center gap-1">
                                    <i class="ph ph-check-circle-fill"></i> Activo
                                </span>
                            @endif
                        </div>
                        <h3 class="font-extrabold text-slate-900 mt-2 text-base leading-snug">{{ $c->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Semestre: {{ $c->semester }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold {{ $isSelected ? 'text-red-700' : 'text-slate-600' }}">
                        <span>{{ $c->theoryGroups->count() }} Teorías</span>
                        <span class="flex items-center gap-1">Ver aforos <i class="ph ph-arrow-right"></i></span>
                    </div>
                </a>
            @empty
                <div class="col-span-3 p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400">
                    No hay cursos registrados.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Ocupación de Laboratorios Desglosada (Con Justificados Únicos) -->
    @if($selectedCourse)
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-red-700 font-mono">{{ $selectedCourse->code_course }}</span>
                <h2 class="text-2xl font-black text-slate-900 mt-0.5">{{ $selectedCourse->name }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('enrollments.index', ['course_id' => $selectedCourse->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white text-xs sm:text-sm font-bold rounded-xl transition">
                    Ver Lista de Matrículas <i class="ph ph-arrow-right"></i>
                </a>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="ph ph-chart-bar text-red-700 text-lg"></i> Ocupación de Laboratorios de Práctica
                </h3>
                <span class="text-xs text-slate-400 font-medium">Base: 15 PCs | Cobertura dinámica por Laptop y Permiso Docente</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($practiceGroups as $grp)
                    @php
                        $alumnos = $grp->enrollments_count;
                        $laptops = $grp->laptop_count;
                        $permisos = $grp->teacher_auth_count;
                        $justificados = $grp->justified_count;
                        
                        // Capacidad real efectiva considerando personas físicas únicas justificadas
                        $capacidadEfectiva = 15 + $justificados;
                        $sobrecupoReal = max(0, $alumnos - $capacidadEfectiva);
                        $porcentaje = min(100, round(($alumnos / max(15, $capacidadEfectiva)) * 100));

                        if ($sobrecupoReal > 0) {
                            $estadoTexto = "+{$sobrecupoReal} Sobrecupo Crítico";
                            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                            $barClass = 'bg-rose-600';
                        } elseif ($alumnos > 15) {
                            $estadoTexto = "Cubierto ({$alumnos}/{$capacidadEfectiva})";
                            $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                            $barClass = 'bg-emerald-600';
                        } elseif ($alumnos == 15) {
                            $estadoTexto = "Lleno Base (15/15)";
                            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                            $barClass = 'bg-amber-500';
                        } else {
                            $disponibles = 15 - $alumnos;
                            $estadoTexto = "{$disponibles} Vacantes Base";
                            $badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
                            $barClass = 'bg-blue-600';
                        }
                    @endphp

                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3.5 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $grp->theoryGroup->name == 'Teoría 2' ? 'bg-purple-600' : 'bg-blue-600' }}"></span>
                                    <h4 class="font-black text-slate-900 text-sm">{{ $grp->code }} ({{ $grp->theoryGroup->name }})</h4>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Horario: {{ $grp->schedule ?? 'Por asignar' }}</p>
                            </div>
                            <span class="text-sm font-black font-mono {{ $sobrecupoReal > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $alumnos }} <span class="text-slate-400 font-normal text-xs">/ 15</span>
                            </span>
                        </div>
                        
                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div class="{{ $barClass }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                        </div>

                        <!-- Indicadores de Laptop, Permisos y Estado -->
                        <div class="pt-2 border-t border-slate-200/60 space-y-2">
                            <div class="flex items-center justify-between text-xs text-slate-600">
                                <div class="flex items-center gap-1 {{ $laptops > 0 ? 'text-purple-700 font-bold' : 'text-slate-400' }}">
                                    <i class="ph ph-laptop text-base"></i>
                                    <span>{{ $laptops }} laptops</span>
                                </div>
                                <div class="flex items-center gap-1 {{ $permisos > 0 ? 'text-indigo-700 font-bold' : 'text-slate-400' }}">
                                    <i class="ph ph-certificate text-base"></i>
                                    <span>{{ $permisos }} permisos</span>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                    {{ $estadoTexto }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-xs col-span-3">No hay grupos de práctica registrados.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection