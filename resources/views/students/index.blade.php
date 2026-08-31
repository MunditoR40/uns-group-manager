@extends('layouts.app')

@section('title', 'Padrón de Estudiantes - UNS')

@section('content')
<div class="space-y-8">
    <!-- Header Hero Institucional -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-red-100 border border-white/10">
                Padrón Oficial SIIGAA • UNS
            </span>
            <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-3 leading-tight">Padrón de Estudiantes</h1>
            <p class="text-slate-300 text-sm sm:text-base mt-2 font-normal">
                Consulta y gestiona los datos de los estudiantes, sus códigos institucionales y la designación oficial de delegados.
            </p>
        </div>
        <div class="absolute -right-6 -bottom-10 opacity-10 text-white pointer-events-none">
            <i class="ph ph-student text-[220px]"></i>
        </div>
    </div>

    <!-- KPIs del Padrón -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-users"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Estudiantes</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['total'] }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-shield-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Delegados Designados</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['delegates'] }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center text-2xl font-bold">
                <i class="ph ph-graduation-cap"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Estudiantes Regulares</p>
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $stats['students'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('students.index') }}" class="flex-1 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, código o correo institucional..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>

            <select name="role" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">Todos los Roles</option>
                <option value="estudiante" {{ request('role') === 'estudiante' ? 'selected' : '' }}>Solo Estudiantes Regulares</option>
                <option value="delegado" {{ request('role') === 'delegado' ? 'selected' : '' }}>Solo Delegados</option>
            </select>

            <button type="submit" class="px-5 py-2.5 bg-red-800 hover:bg-red-900 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                <i class="ph ph-funnel"></i> Filtrar
            </button>

            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('students.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Tabla de Estudiantes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase font-black tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Estudiante</th>
                        <th class="px-6 py-4">Código UNS</th>
                        <th class="px-6 py-4">Correo Institucional</th>
                        <th class="px-6 py-4 text-center">Rol Oficial</th>
                        <th class="px-6 py-4 text-center">Cursos</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs {{ $st->role === 'delegado' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ strtoupper(substr($st->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $st->name }}</div>
                                        <div class="text-[11px] text-slate-400">Ing. de Sistemas e Informática</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-700">
                                {{ $st->code ?? 'Sin código' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-500">
                                {{ $st->email }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($st->role === 'delegado')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 font-extrabold text-[11px] rounded-full">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Delegado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-600 font-semibold text-[11px] rounded-full">
                                        Estudiante
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-extrabold text-slate-800">
                                <span class="px-2.5 py-1 bg-slate-100 rounded-lg">{{ $st->enrollments_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('students.edit', $st->id) }}" 
                                       class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-red-700 transition border border-slate-200 text-xs font-bold flex items-center gap-1"
                                       title="Editar datos del estudiante">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                        <span class="hidden sm:inline">Editar</span>
                                    </a>

                                    <form method="POST" action="{{ route('students.toggle-delegate', $st->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 rounded-xl {{ $st->role === 'delegado' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }} transition text-xs font-bold"
                                                title="{{ $st->role === 'delegado' ? 'Quitar rol delegado' : 'Designar como Delegado' }}">
                                            <i class="ph {{ $st->role === 'delegado' ? 'ph-user-minus' : 'ph-shield-check' }} text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="ph ph-users text-4xl block mb-2 opacity-50"></i>
                                No se encontraron estudiantes con los criterios especificados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection