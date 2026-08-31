<nav class="bg-white border-b border-slate-200 sticky top-0 z-20 backdrop-blur-md bg-white/95 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo & Brand -->
            <div class="flex items-center gap-3">
                <a href="{{ route('courses.index') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-900 to-red-600 text-white flex items-center justify-center font-black text-lg shadow-sm shadow-red-200 group-hover:scale-105 transition-transform">
                        UNS
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 text-base sm:text-lg leading-tight block">Gestor de Grupos</span>
                        <span class="text-[11px] font-medium text-slate-400 hidden sm:block">Universidad Nacional del Santa</span>
                    </div>
                </a>
            </div>

            <!-- Enlaces de Escritorio -->
            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-700 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5">
                    <i class="ph ph-chart-pie-slice text-lg"></i> Dashboard
                </a>
                <a href="{{ route('courses.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-700 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5">
                    <i class="ph ph-squares-four text-lg"></i> Cursos
                </a>
                <a href="{{ route('students.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-700 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5">
                    <i class="ph ph-users text-lg"></i> Estudiantes
                </a>
                <a href="{{ route('teachers.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-700 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5">
                    <i class="ph ph-chalkboard-teacher text-lg"></i> Docentes
                </a>
                <a href="{{ route('audit.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-700 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5">
                    <i class="ph ph-clock-counter-clockwise text-lg"></i> Auditoría & Rollback
                </a>
                <a href="{{ route('exports.enrollments.excel') }}" title="Reporte consolidado del sistema completo (Delegado General)" class="px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 rounded-lg transition flex items-center gap-1.5 border border-emerald-200">
                    <i class="ph ph-file-xls text-lg"></i> Excel General
                </a>
                
                <div class="h-5 w-px bg-slate-200 mx-2"></div>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-full border border-slate-200 text-xs font-semibold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i class="ph ph-user"></i> Delegado
                </div>
            </div>

            <!-- Botón Menú Móvil -->
            <div class="flex items-center md:hidden">
                <button id="btn-mobile-menu" type="button" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 focus:outline-none transition">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú Desplegable Móvil -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white/95 backdrop-blur px-4 pt-2 pb-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
            <i class="ph ph-chart-pie-slice text-lg"></i> Dashboard
        </a>
        <a href="{{ route('courses.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
            <i class="ph ph-squares-four text-lg"></i> Cursos
        </a>
        <a href="{{ route('students.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
            <i class="ph ph-users text-lg"></i> Estudiantes
        </a>
        <a href="{{ route('teachers.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
            <i class="ph ph-chalkboard-teacher text-lg"></i> Docentes
        </a>
        <a href="{{ route('audit.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
            <i class="ph ph-clock-counter-clockwise text-lg"></i> Auditoría & Rollback
        </a>
        <a href="{{ route('exports.enrollments.excel') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-emerald-700 hover:bg-emerald-50 transition">
            <i class="ph ph-file-xls text-lg"></i> Excel General
        </a>
    </div>
</nav>
