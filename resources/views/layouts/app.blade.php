<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Gestión de Grupos UNS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">
    <nav class="bg-red-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="font-bold text-lg tracking-tight">UNS • Control y Auditoría de Matrículas</span>
            </div>
            <div class="flex items-center space-x-4 text-sm">
                <a href="{{ url('/audit') }}" class="hover:text-red-200 transition font-medium">Bitácora</a>
            </div>
        </div>
    </nav>

    <main class="py-6">
        @yield('content')
        {{ $slot ?? '' }}
    </main>
</body>
</html>
