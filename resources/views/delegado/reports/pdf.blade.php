<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padrón de Matriculados - {{ $course->name ?? 'UNS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-8 font-sans">
    <div class="no-print flex justify-between items-center mb-6 pb-4 border-b border-slate-200">
        <span class="text-sm font-semibold text-slate-600">Vista de Impresión / Exportación PDF</span>
        <button onclick="window.print()" class="px-5 py-2.5 bg-red-800 hover:bg-red-900 text-white font-bold text-sm rounded-xl shadow transition flex items-center gap-2">
            🖨️ Descargar como PDF / Imprimir
        </button>
    </div>

    <!-- Cabecera Institucional -->
    <div class="border-b-2 border-slate-900 pb-3 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-black uppercase tracking-tight text-slate-900">UNIVERSIDAD NACIONAL DEL SANTA</h1>
                <p class="text-xs font-semibold text-slate-600">Dirección de Asuntos Académicos • Sistema de Asignación de Laboratorios</p>
            </div>
            <div class="text-right text-xs font-mono text-slate-600">
                <p>Fecha de emisión: {{ date('d/m/Y H:i') }}</p>
                <p>Semestre: {{ $course->semester ?? '2026-II' }}</p>
            </div>
        </div>
    </div>

    <!-- Ficha de Datos -->
    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 mb-4 flex justify-between text-xs">
        <div>
            <span class="font-bold text-slate-500 uppercase">Asignatura:</span>
            <span class="font-black text-slate-900 ml-1">{{ $course->name }} ({{ $course->code_course }})</span>
        </div>
        <div>
            <span class="font-bold text-slate-500 uppercase">Total de Estudiantes:</span>
            <span class="font-black text-slate-900 ml-1">{{ $enrollments->count() }}</span>
        </div>
    </div>

    <!-- Tabla -->
    <table class="w-full text-left text-xs border border-slate-300">
        <thead class="bg-slate-100 uppercase font-bold border-b border-slate-300">
            <tr>
                <th class="p-2 border-r border-slate-300 w-8 text-center">N°</th>
                <th class="p-2 border-r border-slate-300 w-24">Código</th>
                <th class="p-2 border-r border-slate-300">Apellidos y Nombres</th>
                <th class="p-2 border-r border-slate-300 text-center">Teoría</th>
                <th class="p-2 border-r border-slate-300 text-center">Grupo</th>
                <th class="p-2 border-r border-slate-300 text-center">Laptop</th>
                <th class="p-2 border-r border-slate-300 text-center">Permiso</th>
                <th class="p-2 text-center">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($enrollments as $i => $e)
            <tr>
                <td class="p-2 border-r border-slate-200 text-center font-mono">{{ $i + 1 }}</td>
                <td class="p-2 border-r border-slate-200 font-mono font-bold">{{ $e->user->code ?? 'S/C' }}</td>
                <td class="p-2 border-r border-slate-200 font-medium">{{ $e->user->name }}</td>
                <td class="p-2 border-r border-slate-200 text-center">{{ $e->practiceGroup->theoryGroup->name ?? 'Teoría 1' }}</td>
                <td class="p-2 border-r border-slate-200 text-center font-bold">{{ $e->practiceGroup->code }}</td>
                <td class="p-2 border-r border-slate-200 text-center">{{ $e->has_laptop ? 'SÍ' : '-' }}</td>
                <td class="p-2 border-r border-slate-200 text-center">{{ $e->teacher_authorized ? 'SÍ' : '-' }}</td>
                <td class="p-2 text-center uppercase text-[10px] font-bold">{{ $e->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>