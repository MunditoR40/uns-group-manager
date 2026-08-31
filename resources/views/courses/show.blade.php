@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Cabecera del Curso -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $course->name }}</h1>
        <p class="text-sm text-gray-500">Código: {{ $course->code }} | Semestre: {{ $course->semester }}</p>
    </div>

    <!-- TARJETAS DE PROGRESO DE AFORO (Aforo Actual vs Aforo Efectivo) -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Aforo de Grupos de Práctica (Actual vs Efectivo)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($practiceGroups as $group)
                @php
                    $currentCount = $group->enrollments_count; // Aprovecha el conteo optimizado
                    $maxCapacity = 15; 
                    $percentage = min(round(($currentCount / $maxCapacity) * 100), 100);
    
                    $barColor = 'bg-blue-600';
                    if ($percentage >= 100) {
                        $barColor = 'bg-red-500';
                    } elseif ($percentage >= 80) {
                        $barColor = 'bg-yellow-500';
                    }
                @endphp 
               <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-gray-700 text-lg">{{ $group->code }}</span>
                        <span class="text-xs font-semibold px-2 py-1 bg-indigo-50 text-indigo-700 rounded">
                            {{ $group->theoryGroup->name ?? 'Teoría' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Matriculados / Aforo</span>
                        <span class="font-bold {{ $currentCount > $maxCapacity ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $currentCount }} / {{ $maxCapacity }}
                        </span>
                    </div>
                    <!-- Barra de progreso -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                        <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 text-right">Aforo efectivo: 15 alumnos</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- BARRA DE FILTROS Y BÚSQUEDA -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('courses.show', $course) }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            
            <!-- Búsqueda por nombre o código -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Estudiante</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                    placeholder="Nombre o código..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <!-- Filtro por Grupo de Práctica -->
            <div>
                <label for="practice_group_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo Práctica</label>
                <select name="practice_group_id" id="practice_group_id" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="">Todos</option>
                    @foreach($practiceGroups as $group)
                        <option value="{{ $group->id }}" {{ request('practice_group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Grupo de Teoría -->
            <div>
                <label for="theory_group_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo Teoría</label>
                <select name="theory_group_id" id="theory_group_id" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="">Todas</option>
                    @foreach($theoryGroups as $tGroup)
                        <option value="{{ $tGroup->id }}" {{ request('theory_group_id') == $tGroup->id ? 'selected' : '' }}>
                            {{ $tGroup->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Ordenamiento -->
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
                <select name="sort" id="sort" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="fifo" {{ request('sort') == 'fifo' ? 'selected' : '' }}>FIFO (Fecha)</option>
                    <option value="alphabetical_asc" {{ request('sort') == 'alphabetical_asc' ? 'selected' : '' }}>Alfabético (A-Z)</option>
                    <option value="alphabetical_desc" {{ request('sort') == 'alphabetical_desc' ? 'selected' : '' }}>Alfabético (Z-A)</option>
                    <option value="code" {{ request('sort') == 'code' ? 'selected' : '' }}>Código Univ.</option>
                </select>
            </div>

            <!-- Botones de Acción -->
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium transition shadow">
                    Filtrar
                </button>
                <a href="{{ route('courses.show', $course) }}" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-200 text-sm font-medium transition border text-center">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- TABLA INTERACTIVA DE ESTUDIANTES MATRICULADOS -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo Práctica</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Matrícula (FIFO)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($enrollments as $enrollment)
                <tr>
                <!-- 1. Código del estudiante (vía relación user o el campo directo si lo tienes) -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $enrollment->user->code ?? $enrollment->code ?? 'N/A' }}
                    </td>

                <!-- Estudiante -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ $enrollment->user->name ?? 'Sin nombre' }}
                    </td>

                <!-- 2. Grupo de Práctica -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $enrollment->practiceGroup ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $enrollment->practiceGroup->code ?? 'Sin grupo' }}
                        </span>
                    </td>

                <!-- Teoría -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ $enrollment->practiceGroup->theoryGroup->name ?? 'Teoría' }}
                    </td>

                <!-- Fecha Matrícula -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $enrollment->enrolled_at }}
                    </td>

                <!-- Acciones -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <!-- Tu botón de gestionar -->
                    </td>
                </tr>
            @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación Limpia (->paginate(25)) -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $enrollments->links() }}
        </div>
    </div>

</div>
@endsection