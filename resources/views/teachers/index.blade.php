@extends('layouts.app')

@section('title', 'Plana Docente UNS')

@section('content')
<div class="space-y-8" x-data="{
    openModal: false,
    editMode: false,
    teacherId: null,
    name: '',
    code: '',
    email: '',
    department: 'DAISI - Ingeniería de Sistemas e Informática',
    condition: 'Nombrado Principal',

    openCreate() {
        this.editMode = false;
        this.teacherId = null;
        this.name = '';
        this.code = '';
        this.email = '';
        this.department = 'DAISI - Ingeniería de Sistemas e Informática';
        this.condition = 'Nombrado Principal';
        this.openModal = true;
    },

    openEdit(teacher) {
        this.editMode = true;
        this.teacherId = teacher.id;
        this.name = teacher.name;
        this.code = teacher.code || '';
        this.email = teacher.email;
        this.department = teacher.department;
        this.condition = teacher.condition;
        this.openModal = true;
    }
}">
    <!-- Header Hero Institucional -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-slate-200 border border-white/10">
                Reglamento Académico UNS • Carga Lectiva
            </span>
            <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-3 leading-tight">Plana Docente y Asignaciones</h1>
            <p class="text-slate-200 text-sm sm:text-base mt-2 font-normal">
                Control de carga lectiva y supervisión de la <strong class="text-white">Regla Oficial UNS</strong>: Un docente no puede dictar 2 teorías en un mismo ciclo, pero puede asumir múltiples prácticas para completar su carga.
            </p>
        </div>
        <div class="absolute -right-6 -bottom-10 opacity-10 text-white pointer-events-none">
            <i class="ph ph-chalkboard-teacher text-[220px]"></i>
        </div>
    </div>

    <!-- Barra de Acciones y Métricas -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Docentes Registrados</h2>
            <p class="text-xs text-slate-500">Supervisa las teorías y laboratorios asignados a cada profesor.</p>
        </div>

        <button @click="openCreate()" type="button" 
                class="px-4 py-2.5 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
            <i class="ph ph-plus-circle text-base"></i>
            <span>Registrar Docente</span>
        </button>
    </div>

    <!-- Tabla de Plana Docente -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-bold text-[10px]">
                        <th class="py-4 px-6">Docente</th>
                        <th class="py-4 px-6">Departamento / Condición</th>
                        <th class="py-4 px-6">Teorías Asignadas (Regla UNS: Máx 1/Ciclo)</th>
                        <th class="py-4 px-6">Prácticas (Carga Lectiva)</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($teachers as $t)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-700 border border-red-200 flex items-center justify-center font-black text-xs">
                                        {{ substr($t->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ $t->name }}</div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-2">
                                            <span>{{ $t->email }}</span>
                                            @if($t->code)
                                                <span>•</span>
                                                <span class="font-mono text-slate-500 font-bold">{{ $t->code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $t->department }}</div>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ $t->condition }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($t->theoryGroups->count() > 0)
                                    <div class="space-y-1.5">
                                        @foreach($t->theoryGroups as $tg)
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    {{ $tg->course->cycle ?? 'II Ciclo' }}
                                                </span>
                                                <a href="{{ route('courses.show', $tg->course_id) }}" class="font-bold text-slate-800 hover:text-red-700 transition">
                                                    {{ $tg->course->name }} ({{ $tg->name }})
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Sin teoría asignada</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($t->practiceGroups->count() > 0)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($t->practiceGroups as $pg)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="{{ $pg->schedule }}">
                                                {{ $pg->theoryGroup->course->code_course }}: {{ $pg->code }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">0 prácticas</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click='openEdit(@json($t))' type="button" 
                                            class="p-2 rounded-lg text-slate-600 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 transition" title="Editar Docente">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </button>

                                    @if($t->theory_groups_count === 0 && $t->practice_groups_count === 0)
                                        <form action="{{ route('teachers.destroy', $t) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a este docente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-slate-600 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition" title="Eliminar Docente">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="ph ph-users text-4xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">No se encontraron docentes registrados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Registrar / Editar Docente -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;" x-cloak>
        <div @click.away="openModal = false" class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 relative">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-black text-slate-900" x-text="editMode ? 'Editar Datos del Docente' : 'Registrar Nuevo Docente'"></h3>
                    <p class="text-xs text-slate-500 mt-1">Ingresa los datos oficiales del docente según el padrón de la UNS.</p>
                </div>
                <button @click="openModal = false" type="button" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <form :action="editMode ? '/teachers/' + teacherId : '{{ route('teachers.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Apellidos y Nombres</label>
                    <input type="text" name="name" x-model="name" required placeholder="Ej: ING. BORJA ROSALES WHISTON"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Código Docente</label>
                        <input type="text" name="code" x-model="code" placeholder="Ej: DOC-103"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Correo Institucional</label>
                        <input type="email" name="email" x-model="email" required placeholder="docente@uns.edu.pe"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Departamento Académico</label>
                    <select name="department" x-model="department" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500">
                        <option value="DAISI - Ingeniería de Sistemas e Informática">DAISI - Ingeniería de Sistemas e Informática</option>
                        <option value="DAMA - Departamento de Matemática">DAMA - Departamento de Matemática</option>
                        <option value="DAEF - Departamento de Física">DAEF - Departamento de Física</option>
                        <option value="DAIA - Agroindustria y Alimentos">DAIA - Agroindustria y Alimentos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Condición Laboral</label>
                    <select name="condition" x-model="condition" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-red-500">
                        <option value="Nombrado Principal">Nombrado Principal</option>
                        <option value="Nombrado Asociado">Nombrado Asociado</option>
                        <option value="Nombrado Auxiliar">Nombrado Auxiliar</option>
                        <option value="Contratado">Contratado</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                    <button @click="openModal = false" type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-xs font-bold text-white shadow-sm transition">
                        Guardar Docente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection