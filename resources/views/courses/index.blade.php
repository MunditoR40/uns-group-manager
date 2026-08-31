@extends('layouts.app')

@section('title', 'Catálogo de Cursos')

@section('content')
<div class="space-y-8" x-data="{
    openCourseModal: false,
    editCourseMode: false,
    courseId: null,
    code_course: '',
    name: '',
    cycle: 'II Ciclo',
    semester: '2026-02',
    teacher_id: '',
    base_capacity: 15,
    practice_groups_count: 3,

    openCreate() {
        this.editCourseMode = false;
        this.courseId = null;
        this.code_course = '';
        this.name = '';
        this.cycle = 'II Ciclo';
        this.semester = '2026-02';
        this.teacher_id = '';
        this.base_capacity = 15;
        this.practice_groups_count = 3;
        this.openCourseModal = true;
    },

    openEdit(c) {
        this.editCourseMode = true;
        this.courseId = c.id;
        this.code_course = c.code_course;
        this.name = c.name;
        this.cycle = c.cycle || 'II Ciclo';
        this.semester = c.semester || '2026-02';
        this.teacher_id = c.theory_groups && c.theory_groups.length > 0 && c.theory_groups[0].teacher_id ? c.theory_groups[0].teacher_id : '';
        this.openCourseModal = true;
    }
}">
    <!-- Header Hero Institucional -->
    <div class="bg-gradient-to-r from-red-900 via-red-800 to-red-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-red-100 border border-white/10">
                Semestre Académico 2026-II • Sistema SIIGAA UNS
            </span>
            <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-3 leading-tight">Panel del Delegado y Asignaturas</h1>
            <p class="text-red-100 text-sm sm:text-base mt-2 font-normal">
                Monitorea el aforo de laboratorios, balancea inscripciones, asigna la plana docente y exporta padrones oficiales en Excel.
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

    <!-- Barra de Acciones y Filtros del Catálogo -->
    <div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Asignaturas Registradas</h2>
                <p class="text-xs text-slate-500">Haz clic en cualquier asignatura para gestionar sus grupos de teoría y práctica.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Selector de Ciclos (Pills Interactivas) -->
                <div class="flex items-center gap-1.5 p-1 bg-white border border-slate-200 rounded-2xl shadow-sm">
                    <a href="{{ route('courses.index', ['cycle' => 'all']) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ (!request('cycle') || request('cycle') === 'all') ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800 hover:bg-slate-50' }}">
                        <i class="ph ph-squares-four"></i>
                        <span>Todos</span>
                    </a>
                    @foreach($allCycles as $cName)
                        @php
                            $colorDot = match(strtoupper($cName)) {
                                'II CICLO' => 'bg-blue-500',
                                'IV CICLO' => 'bg-purple-500',
                                'VI CICLO' => 'bg-emerald-500',
                                'VIII CICLO' => 'bg-amber-500',
                                default => 'bg-rose-500'
                            };
                        @endphp
                        <a href="{{ route('courses.index', ['cycle' => $cName]) }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('cycle') === $cName ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800 hover:bg-slate-50' }}">
                            <span class="w-2 h-2 rounded-full {{ $colorDot }}"></span>
                            <span>{{ $cName }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Botón de Jared: Nueva Asignatura -->
                <button @click="openCreate()" type="button" 
                        class="px-4 py-2 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-2xl shadow-sm transition flex items-center gap-2">
                    <i class="ph ph-plus-circle text-base"></i>
                    <span>Nueva Asignatura</span>
                </button>
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
                    $theoryTeacher = $c->theoryGroups->first()?->teacher;
                @endphp
                <div onclick="window.location.href='{{ route('courses.show', $c) }}'"
                     title="Haz clic para gestionar {{ $c->name }}"
                     class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-xl hover:border-red-400 hover:-translate-y-1.5 transition-all duration-200 flex flex-col justify-between group cursor-pointer relative">
                    
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="px-2.5 py-1 text-xs font-bold font-mono rounded-lg bg-red-50 text-red-700 border border-red-200">
                                {{ $c->code_course }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $isFourthCycle ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $c->cycle ?? 'II Ciclo' }}
                                </span>
                                <!-- Menú de edición y borrado de curso -->
                                <button onclick="event.stopPropagation();" @click='openEdit(@json($c))' type="button" 
                                        class="p-1 rounded-lg text-slate-400 hover:text-blue-700 hover:bg-slate-100 transition" title="Editar Asignatura">
                                    <i class="ph ph-pencil-simple text-base"></i>
                                </button>
                                @if($c->enrollments_count === 0)
                                    <form action="{{ route('courses.destroy', $c) }}" method="POST" onclick="event.stopPropagation();" onsubmit="return confirm('¿Seguro que deseas eliminar la asignatura {{ $c->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-red-700 hover:bg-red-50 transition" title="Eliminar Asignatura">
                                            <i class="ph ph-trash text-base"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-red-700 transition">
                            {{ $c->name }}
                        </h3>

                        <!-- Docente de Teoría Asignado -->
                        <div class="mt-2 text-xs flex items-center gap-1.5 text-slate-500">
                            <i class="ph ph-chalkboard-teacher text-slate-400"></i>
                            @if($theoryTeacher)
                                <span class="font-bold text-slate-700">{{ $theoryTeacher->name }}</span>
                            @else
                                <span class="italic text-slate-400">Docente por asignar</span>
                            @endif
                        </div>

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

                    <!-- Botones de Acción: Gestionar + Descargar Excel Oficial -->
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-2">
                        <div class="flex-1 py-2.5 px-3 bg-red-800 group-hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                            <span>Gestionar</span>
                            <i class="ph ph-arrow-right font-bold group-hover:translate-x-1 transition-transform"></i>
                        </div>

                        <!-- Botón Excel del Curso (Directo y Exclusivo) -->
                        <a href="{{ route('courses.excel', $c) }}" 
                           onclick="event.stopPropagation();"
                           title="Descargar Padrón Oficial en Excel de {{ $c->name }} (Consolidado + Teorías)"
                           class="py-2.5 px-3 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 hover:border-emerald-600 rounded-xl transition flex items-center justify-center font-bold text-xs gap-1">
                            <i class="ph ph-file-xls text-lg"></i>
                            <span>Excel</span>
                        </a>
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

    <!-- Modal Registrar / Editar Asignatura (CRUD de Jared) -->
    <template x-teleport="body">
        <div x-show="openCourseModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md" style="display: none;" x-cloak>
        <div @click.away="openCourseModal = false" class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 relative">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-black text-slate-900" x-text="editCourseMode ? 'Editar Asignatura' : 'Registrar Nueva Asignatura'"></h3>
                    <p class="text-xs text-slate-500 mt-1">Configuración oficial del curso y asignación docente según regla UNS.</p>
                </div>
                <button @click="openCourseModal = false" type="button" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <form :action="editCourseMode ? '/courses/' + courseId : '{{ route('courses.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editCourseMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Código Curso</label>
                        <input type="text" name="code_course" x-model="code_course" required placeholder="1411-0030"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-red-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Nombre del Curso</label>
                        <input type="text" name="name" x-model="name" required placeholder="Ej: SISTEMAS DISTRIBUIDOS"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Ciclo Académico</label>
                        <select name="cycle" x-model="cycle" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500">
                            <option value="II Ciclo">II Ciclo (Segundo)</option>
                            <option value="IV Ciclo">IV Ciclo (Cuarto)</option>
                            <option value="VI Ciclo">VI Ciclo (Sexto)</option>
                            <option value="VIII Ciclo">VIII Ciclo (Octavo)</option>
                            <option value="X Ciclo">X Ciclo (Décimo)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Semestre</label>
                        <input type="text" name="semester" x-model="semester" required placeholder="2026-02"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Docente de Teoría (Regla UNS: Máx 1/Ciclo)</label>
                    <select name="teacher_id" x-model="teacher_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500">
                        <option value="">-- Seleccionar Docente Responsable --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->department }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">El sistema impedirá que el docente sea asignado a otra teoría en el mismo ciclo.</p>
                </div>

                <template x-if="!editCourseMode">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 bg-slate-50 rounded-2xl border border-slate-200/60">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Aforo Base por Práctica</label>
                            <input type="number" name="base_capacity" x-model="base_capacity" min="5" max="50" required
                                   class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">N° Grupos de Práctica</label>
                            <input type="number" name="practice_groups_count" x-model="practice_groups_count" min="1" max="8" required
                                   class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-900">
                        </div>
                    </div>
                </template>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                    <button @click="openCourseModal = false" type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-xs font-bold text-white shadow-sm transition">
                        <span x-text="editCourseMode ? 'Guardar Cambios' : 'Crear Asignatura'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>
@endsection