<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestión de Grupos') - UNS</title>

    <!-- Tailwind CSS CDN con paleta institucional UNS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        uns: {
                            50: '#fdf2f2',
                            100: '#fde8e8',
                            600: '#c81e1e',
                            700: '#9b1c1c',
                            800: '#800000',
                            900: '#660000',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased selection:bg-red-700 selection:text-white">

    <!-- Barra de Navegación -->
    @include('layouts.navigation')

    <!-- Alertas Flash Globales -->
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        @if(session('success'))
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 shadow-sm flex items-center gap-3">
                <i class="ph ph-check-circle text-2xl text-emerald-600 flex-shrink-0"></i>
                <div>
                    <h4 class="text-sm font-bold">Operación Exitosa</h4>
                    <p class="text-xs text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900 shadow-sm flex items-center gap-3">
                <i class="ph ph-warning-diamond text-2xl text-rose-600 flex-shrink-0"></i>
                <div>
                    <h4 class="text-sm font-bold">Aviso</h4>
                    <p class="text-xs text-rose-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Contenido Principal -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- Pie de página Institucional -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Sistema de Gestión y Reasignación de Grupos • Escuela Profesional de Ingeniería de Sistemas e Informática (UNS)
    </footer>

    <!-- Script Menú Móvil -->
    <script>
        document.getElementById('btn-mobile-menu')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
