@extends('layouts.app')

@section('title', 'Catálogo de Cursos')

@section('content')
<div class="space-y-8">
    <!-- Header Hero Institucional -->
    <div class="bg-gradient-to-r from-red-900 via-red-800 to-red-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-red-100 border border-white/10">
                Semestre Académico 2026-II • Sistema SIIGAA UNS
            </span>
            <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-3 leading-tight">Panel del Delegado y Asignaturas</h1>
            <p class="text-red-100 text-sm sm:text-base mt-2 font-normal">
                Monitorea el aforo de laboratorios, balancea inscripciones y ejecuta la partición formal de teorías y prácticas.
            </p>
        </div>
        <div class="absolute -right-6 -bottom-10 opacity-10 text-white pointer-events-none">
            <i class="ph ph-buildings text-[220px]"></i>
        </div>
    </div>

    <!-- Tarjetas de Métricas Globales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-users"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Estudiantes</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['total_students'] }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-laptop"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Equipos Laptop</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['total_laptops'] }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-certificate"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Permisos Docentes</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['total_authorized'] }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-arrows-left-right"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Reasignados</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['total_reassigned'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Catálogo de Cursos Oficiales con Filtro de Ciclos -->
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Asignaturas Registradas</h2>
                <p class="text-xs text-slate-500">Haz clic en cualquier asignatura para gestionar sus grupos de teoría y práctica.</p>
            </div>

            <!-- Filtro de Ciclos Interactivo -->
            <div class="flex items-center gap-1.5 p-1 bg-white border border-slate-200 rounded-2xl shadow-sm self-start sm:self-auto">
                <a href="{{ route('courses.index', ['cycle' => 'all']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ (!request('cycle') || request('cycle') === 'all') ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800 hover:bg-slate-50' }}">
                    <i class="ph ph-squares-four"></i>
                    <span>Todos</span>
                </a>
                <a href="{{ route('courses.index', ['cycle' => 'II Ciclo']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('cycle') === 'II Ciclo' ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800 hover:bg-slate-50' }}">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Segundo Ciclo</span>
                </a>
                <a href="{{ route('courses.index', ['cycle' => 'IV Ciclo']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('cycle') === 'IV Ciclo' ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800 hover:bg-slate-50' }}">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span>Cuarto Ciclo</span>
                </a>
            </div>
        </div>

        <!-- Rejilla de Cursos Interactivos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($courses as $c)
                @php
                    $theoriesCount = $c->theoryGroups->count();
                    $practicesCount = $c->practiceGroups->count();
                    $isSplittable = ($c->enrollments_count >= 60 && $theoriesCount < 2);
                    $isSplitted = ($theoriesCount >= 2);
                    $isFourthCycle = ($c->cycle === 'IV Ciclo');
                @endphp
                <div onclick="window.location.href='{{ route('courses.show', $c) }}'"
                     title="Haz clic para gestionar {{ $c->name }}"
                     class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-xl hover:border-red-400 hover:-translate-y-1.5 transition-all duration-200 flex flex-col justify-between group cursor-pointer relative">
                    
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="px-2.5 py-1 text-xs font-bold font-mono rounded-lg bg-red-50 text-red-700 border border-red-200">
                                {{ $c->code_course }}
                            </span>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $isFourthCycle ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $c->cycle ?? 'II Ciclo' }}
                            </span>
                        </div>

                        <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-red-700 transition">
                            {{ $c->name }}
                        </h3>

                        <div class="mt-4 space-y-2 text-xs text-slate-600">
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <span class="text-slate-400">Matriculados:</span>
                                <span class="font-bold text-slate-800">{{ $c->enrollments_count }} estudiantes</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <span class="text-slate-400">Grupos de Teoría:</span>
                                <span class="font-bold text-slate-800">{{ $theoriesCount }} {{ $theoriesCount === 1 ? 'Teoría' : 'Teorías' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <span class="text-slate-400">Laboratorios / Prácticas:</span>
                                <span class="font-bold text-slate-800">{{ $practicesCount }} grupos</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            @if($isSplittable)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="ph ph-warning-circle"></i> Apto para División T1 → T2 (≥60)
                                </span>
                            @elseif($isSplitted)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200">
                                    <i class="ph ph-check-circle"></i> Teorías Divididas (T1 y T2)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="ph ph-info"></i> Régimen Regular (<60)
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <div class="w-full py-2.5 px-4 bg-red-800 group-hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                            <span>Gestionar Asignatura</span>
                            <i class="ph ph-arrow-right font-bold group-hover:translate-x-1.5 transition-transform"></i>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400">
                    <i class="ph ph-folder-open text-4xl mb-2 text-slate-300"></i>
                    <p class="text-sm font-semibold">No se encontraron asignaturas para el ciclo seleccionado.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
