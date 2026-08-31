<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Gestión de Grupos UNS - Panel del Delegado' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 flex flex-col">
    
    <!-- Barra de navegación superior Institucional -->
    <nav class="bg-red-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="font-bold text-lg tracking-tight">UNS • Gestión y Reasignación de Grupos</span>
                <span class="text-xs bg-red-950/60 px-2 py-0.5 rounded text-red-200 border border-red-800">Delegado</span>
            </div>
            <div class="flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('audit.index') }}" class="hover:text-red-200 transition">Bitácora de Auditoría</a>
                <a href="{{ route('exports.enrollments.excel') }}" class="hover:text-red-200 transition text-xs bg-red-800 hover:bg-red-700 px-2.5 py-1 rounded border border-red-700">Excel General</a>
            </div>
        </div>
    </nav>

    <!-- Contenido principal que inyecta cada vista -->
    <main class="flex-grow">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <footer class="bg-white border-t border-gray-200 py-3 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} Sistema de Gestión de Matrícula • Escuela Profesional de Ingeniería de Sistemas e Informática (UNS)
    </footer>

</body>
</html>
