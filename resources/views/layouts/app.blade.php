<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Delegado - UNS</title>
    <!-- Tailwind CSS (CDN para desarrollo rápido) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Barra de navegación superior -->
    <nav class="bg-indigo-700 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
            <span class="font-bold text-lg">UNS - Panel de Gestión de Matrícula</span>
            <span class="text-xs bg-indigo-800 px-2.5 py-1 rounded">Rol: Delegado</span>
        </div>
    </nav>

    <!-- Contenido principal que inyecta cada vista -->
    <main>
        @yield('content')
    </main>

</body>
</html>