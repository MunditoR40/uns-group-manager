@extends('layouts.app')

@section('title', 'Dashboard de Estadísticas Académicas')

@section('content')
<div class="space-y-8">
    <!-- Header Hero Institucional -->
    <div class="bg-gradient-to-r from-slate-900 via-red-950 to-red-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-red-100 border border-white/10">
                Sistema SIIGAA UNS • Analítica Institucional
            </span>
            <h1 class="text-2xl sm:text-4xl font-black tracking-tight mt-3 leading-tight">Dashboard y Métricas en Tiempo Real</h1>
            <p class="text-red-100 text-sm sm:text-base mt-2 font-normal">
                Supervisa la ocupación de laboratorios, distribución por promociones de ingreso, índice de laptops y cumplimiento de carga docente.
            </p>
        </div>
        <div class="absolute -right-6 -bottom-10 opacity-10 text-white pointer-events-none">
            <i class="ph ph-chart-pie-slice text-[220px]"></i>
        </div>
    </div>

    <!-- Barra de Selección de Visión: Global vs Curso Específico -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Alcance del Análisis</h2>
            <p class="text-xs text-slate-500">Selecciona si deseas ver los indicadores consolidados de toda la escuela o de una asignatura en particular.</p>
        </div>

        <div class="flex items-center gap-3">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Filtrar por Asignatura:</label>
            <select onchange="window.location.href='/dashboard' + (this.value ? '?course_id=' + this.value : '')"
                    class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-slate-800 font-bold shadow-sm focus:ring-2 focus:ring-red-500">
                <option value="">-- Toda la Escuela (Consolidado Global) --</option>
                @foreach($allCourses as $c)
                    <option value="{{ $c->id }}" {{ $selectedCourse && $selectedCourse->id === $c->id ? 'selected' : '' }}>
                        {{ $c->code_course }} - {{ $c->name }} ({{ $c->cycle }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tarjetas de KPIs Clave -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Estudiantes</span>
                <i class="ph ph-users text-xl text-blue-600"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-900">{{ $totalStudents }}</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Alumnos únicos matriculados</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Matrículas</span>
                <i class="ph ph-files text-xl text-purple-600"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-900">{{ $totalEnrollments }}</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Inscripciones en laboratorios</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Laptops Propias</span>
                <i class="ph ph-laptop text-xl text-emerald-600"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-slate-900">{{ $totalLaptops }}</h3>
                <span class="text-xs font-bold text-emerald-600">{{ $laptopPercentage }}%</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">Alumnos con equipo portátil</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Reasignados</span>
                <i class="ph ph-arrows-left-right text-xl text-amber-600"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-slate-900">{{ $totalReassigned }}</h3>
                <span class="text-xs font-bold text-amber-600">{{ $reassignedPercentage }}%</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">Balanceados o migrados a T2</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider">Sobrecupo</span>
                <i class="ph ph-warning-circle text-xl {{ $criticalOvercapacityGroups > 0 ? 'text-red-600 animate-pulse' : 'text-slate-400' }}"></i>
            </div>
            <h3 class="text-2xl font-black {{ $criticalOvercapacityGroups > 0 ? 'text-red-600' : 'text-slate-900' }}">
                {{ $criticalOvercapacityGroups }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Grupos con aforo excedido</p>
        </div>
    </div>

    <!-- Fila 1: Promociones de Ingreso + Equipamiento Laptop -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Promociones de Ingreso -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-identification-badge text-lg text-purple-600"></i>
                            <span>Distribución por Promoción de Ingreso</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Permite identificar si los matriculados son de la <strong class="text-slate-700">promoción regular</strong> o de promociones anteriores (repitentes).
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative my-4 flex items-center justify-center" style="height: 270px;">
                <canvas id="chartPromociones"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-around gap-2 text-xs">
                @foreach($promoLabels as $index => $label)
                    <div class="flex items-center gap-1.5 font-semibold text-slate-700">
                        <span class="w-3 h-3 rounded-full {{ $index === 0 ? 'bg-purple-600' : 'bg-blue-600' }}"></span>
                        <span>{{ $label }}: <strong>{{ $promoData[$index] ?? 0 }}</strong> estudiantes</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Gráfico de Laptops vs PCs Fijas -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="ph ph-laptop text-lg text-emerald-600"></i>
                            <span>Equipamiento: Laptop vs PC Fija de Laboratorio</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Sustento técnico para determinar cuántos puestos físicos de laboratorio se requieren con urgencia.
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative my-4 flex items-center justify-center" style="height: 270px;">
                <canvas id="chartLaptops"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-around gap-2 text-xs">
                <div class="flex items-center gap-1.5 font-semibold text-slate-700">
                    <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                    <span>Con Laptop: <strong>{{ $totalLaptops }}</strong> ({{ $laptopPercentage }}%)</span>
                </div>
                <div class="flex items-center gap-1.5 font-semibold text-slate-700">
                    <span class="w-3 h-3 rounded-full bg-slate-400"></span>
                    <span>Requiere PC Lab: <strong>{{ $totalEnrollments - $totalLaptops }}</strong> ({{ 100 - $laptopPercentage }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila 2: Aforos por Grupos de Práctica (Bar Chart Interactivo) -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ph ph-chart-bar text-lg text-red-700"></i>
                    <span>Ocupación de Aforos por Laboratorio / Práctica</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Comparación de alumnos matriculados vs. capacidad base recomendada (15 estudiantes).
                </p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-600"></span> Regular</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-500"></span> Límite (≥15)</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-600"></span> Sobrecupo Crítico</div>
            </div>
        </div>

        <div class="relative w-full" style="height: 320px;">
            <canvas id="chartAforos"></canvas>
        </div>
    </div>

    <!-- Fila 3: Carga Docente + Distribución por Ciclo -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Gráfico de Carga Docente (Horizontal Bar) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ph ph-chalkboard-teacher text-lg text-indigo-600"></i>
                    <span>Carga Lectiva de la Plana Docente (Regla UNS)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Máximo 1 teoría por ciclo académico y distribución de prácticas para completar carga lectiva.
                </p>
            </div>
            <div class="relative w-full" style="height: 280px;">
                <canvas id="chartDocentes"></canvas>
            </div>
        </div>

        <!-- Gráfico de Ciclos Académicos -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="ph ph-graduation-cap text-lg text-red-800"></i>
                    <span>Distribución por Ciclo</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Carga de estudiantes por semestre.</p>
            </div>
            <div class="relative my-4 flex items-center justify-center" style="height: 220px;">
                <canvas id="chartCiclos"></canvas>
            </div>
            <div class="pt-3 border-t border-slate-100 text-xs space-y-1 text-slate-600">
                @foreach($cyclesCount as $cycleName => $count)
                    <div class="flex justify-between font-semibold">
                        <span>{{ $cycleName }}:</span>
                        <span class="text-slate-900 font-extrabold">{{ $count }} alumnos</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Gráfico Circular de Promociones de Ingreso
    const ctxPromo = document.getElementById('chartPromociones')?.getContext('2d');
    if (ctxPromo) {
        new Chart(ctxPromo, {
            type: 'doughnut',
            data: {
                labels: @json($promoLabels),
                datasets: [{
                    data: @json($promoData),
                    backgroundColor: [
                        'rgba(147, 51, 234, 0.85)', // Promo 2025 (Purple)
                        'rgba(37, 99, 235, 0.85)',  // Promo 2026 (Blue)
                        'rgba(13, 148, 136, 0.85)', // Otras (Teal)
                        'rgba(245, 158, 11, 0.85)'  // Amber
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.parsed || 0;
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ` ${context.label}: ${val} estudiantes (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Gráfico de Laptops vs PCs
    const ctxLaptops = document.getElementById('chartLaptops')?.getContext('2d');
    if (ctxLaptops) {
        new Chart(ctxLaptops, {
            type: 'doughnut',
            data: {
                labels: @json($laptopChart['labels']),
                datasets: [{
                    data: @json($laptopChart['data']),
                    backgroundColor: [
                        'rgba(5, 150, 105, 0.85)', // Emerald
                        'rgba(148, 163, 184, 0.85)' // Slate
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                }
            }
        });
    }

    // 3. Gráfico de Aforos de Prácticas (Bar Chart)
    const ctxAforos = document.getElementById('chartAforos')?.getContext('2d');
    if (ctxAforos) {
        new Chart(ctxAforos, {
            type: 'bar',
            data: {
                labels: @json($practiceLabels),
                datasets: [
                    {
                        label: 'Inscritos Actuales',
                        data: @json($practiceEnrolled),
                        backgroundColor: @json($practiceColors),
                        borderRadius: 8,
                        barPercentage: 0.65
                    },
                    {
                        label: 'Capacidad Base (15)',
                        data: @json($practiceCapacity),
                        backgroundColor: 'rgba(203, 213, 225, 0.5)',
                        borderColor: '#94a3b8',
                        borderWidth: 1,
                        borderRadius: 8,
                        barPercentage: 0.65
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Cantidad de Alumnos', font: { weight: 'bold', size: 11 } },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold', size: 10 } }
                    }
                },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                }
            }
        });
    }

    // 4. Gráfico de Carga Docente (Horizontal Bar)
    const ctxDocentes = document.getElementById('chartDocentes')?.getContext('2d');
    if (ctxDocentes) {
        new Chart(ctxDocentes, {
            type: 'bar',
            data: {
                labels: @json($teacherLabels),
                datasets: [
                    {
                        label: 'Grupos de Teoría (Máx 1/Ciclo)',
                        data: @json($teacherTheories),
                        backgroundColor: 'rgba(127, 29, 29, 0.85)', // Red UNS
                        borderRadius: 6
                    },
                    {
                        label: 'Grupos de Práctica (Carga Lectiva)',
                        data: @json($teacherPractices),
                        backgroundColor: 'rgba(79, 70, 229, 0.85)', // Indigo
                        borderRadius: 6
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    y: {
                        ticks: { font: { weight: 'bold', size: 11 } }
                    }
                },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                }
            }
        });
    }

    // 5. Gráfico de Ciclos (Pie Chart)
    const ctxCiclos = document.getElementById('chartCiclos')?.getContext('2d');
    if (ctxCiclos) {
        new Chart(ctxCiclos, {
            type: 'pie',
            data: {
                labels: Object.keys(@json($cyclesCount)),
                datasets: [{
                    data: Object.values(@json($cyclesCount)),
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.85)',  // II Ciclo (Blue)
                        'rgba(147, 51, 234, 0.85)'  // IV Ciclo (Purple)
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection