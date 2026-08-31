@extends('layouts.app')

@section('title', 'Editar Estudiante - ' . $student->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('students.index') }}" class="hover:text-red-700 transition">Estudiantes</a>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ $student->code ?? $student->name }}</span>
        <span>/</span>
        <span class="text-slate-900 font-bold">Editar</span>
    </nav>

    <!-- Card de Edición -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
            <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-700 flex items-center justify-center font-black text-xl">
                {{ strtoupper(substr($student->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-xl font-black text-slate-900">Editar Datos del Estudiante</h2>
                <p class="text-xs text-slate-500 mt-0.5">Actualiza la información oficial registrada en el padrón de la UNS.</p>
            </div>
        </div>

        <form action="{{ route('students.update', $student->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Apellidos y Nombres
                </label>
                <input type="text" name="name" value="{{ old('name', $student->name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Código Institucional UNS
                    </label>
                    <input type="text" name="code" value="{{ old('code', $student->code) }}" required placeholder="0202614032"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('code')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Correo Institucional
                    </label>
                    <input type="email" name="email" value="{{ old('email', $student->email) }}" required placeholder="codigo@uns.edu.pe"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono font-semibold text-slate-900 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Rol Académico Oficial
                </label>
                <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-red-500">
                    <option value="estudiante" {{ old('role', $student->role) === 'estudiante' ? 'selected' : '' }}>
                        Estudiante Regular
                    </option>
                    <option value="delegado" {{ old('role', $student->role) === 'delegado' ? 'selected' : '' }}>
                        Delegado Oficial de Curso / Base
                    </option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">
                    Designar como Delegado otorga visibilidad de gestión y herramientas de balanceo sobre los cursos.
                </p>
                @error('role')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Asignaturas en las que está matriculado actualmente -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 mb-2.5 flex items-center gap-1.5">
                    <i class="ph ph-book-open text-base text-red-700"></i>
                    <span>Asignaturas Matriculadas en el Semestre Activo</span>
                </h3>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                    @if($student->enrollments->count() > 0)
                        <div class="space-y-2">
                            @foreach($student->enrollments as $enr)
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-200/60 last:border-0">
                                    <div>
                                        <span class="font-bold text-slate-900">{{ $enr->course->name ?? 'Curso' }}</span>
                                        <span class="text-slate-400 font-mono text-[11px]">({{ $enr->course->code_course ?? '' }})</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-white border border-slate-200 font-extrabold text-slate-700">
                                            {{ $enr->practiceGroup->code ?? 'Sin grupo' }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $enr->status === 'reasignado' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($enr->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">El estudiante no registra matrículas activas en ningún grupo.</p>
                    @endif
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('students.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                    <i class="ph ph-floppy-disk text-base"></i>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection