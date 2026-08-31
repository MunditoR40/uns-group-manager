<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\TheoryGroup;
use App\Models\User;
use App\Services\ReallocationService;
use App\Services\TeacherAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Catálogo interactivo de Cursos (Dashboard inicial del sistema)
     */
    public function index(Request $request)
    {
        $query = Course::with(['theoryGroups.practiceGroups', 'theoryGroups.teacher'])
            ->withCount('enrollments');

        if ($request->filled('cycle') && $request->cycle !== 'all') {
            $query->where('cycle', $request->cycle);
        }

        $courses = $query->get();
        $allCycles = Course::distinct()->whereNotNull('cycle')->pluck('cycle')->sort()->values();
        $teachers = Teacher::orderBy('name')->get();

        $stats = [
            'total_students' => Enrollment::distinct('user_id')->count('user_id'),
            'total_enrollments' => Enrollment::count(),
            'total_laptops' => Enrollment::where('has_laptop', true)->count(),
            'total_authorized' => Enrollment::where('teacher_authorized', true)->count(),
            'total_reassigned' => Enrollment::where('status', 'reasignado')->count(),
        ];

        return view('courses.index', compact('courses', 'stats', 'allCycles', 'teachers'));
    }

    /**
     * Registro de una nueva asignatura (Entregable Jared completado y robustecido)
     */
    public function store(Request $request, TeacherAssignmentService $teacherService)
    {
        $validated = $request->validate([
            'code_course' => 'required|string|max:50|unique:courses,code_course',
            'name' => 'required|string|max:255',
            'cycle' => 'required|string|max:50',
            'semester' => 'required|string|max:50',
            'teacher_id' => 'nullable|exists:teachers,id',
            'base_capacity' => 'nullable|integer|min:5|max:50',
            'practice_groups_count' => 'nullable|integer|min:1|max:8',
        ]);

        $baseCapacity = $validated['base_capacity'] ?? 15;
        $practiceCount = $validated['practice_groups_count'] ?? 3;

        // Validar Regla Oficial UNS: El docente no puede dictar 2 teorías en el mismo ciclo
        if (!empty($validated['teacher_id'])) {
            $teacher = Teacher::findOrFail($validated['teacher_id']);
            $dummyCourse = new Course(['cycle' => $validated['cycle'], 'name' => $validated['name']]);
            $check = $teacherService->canAssignTheory($teacher, $dummyCourse);
            if (!$check['allowed']) {
                return redirect()->back()->withInput()->with('error', $check['message']);
            }
        }

        $course = DB::transaction(function () use ($validated, $baseCapacity, $practiceCount) {
            $course = Course::create([
                'code_course' => strtoupper(trim($validated['code_course'])),
                'name' => strtoupper(trim($validated['name'])),
                'cycle' => $validated['cycle'],
                'semester' => $validated['semester'],
            ]);

            $theory = TheoryGroup::create([
                'course_id' => $course->id,
                'name' => 'Teoría 1',
                'teacher_id' => $validated['teacher_id'] ?? null,
            ]);

            $letters = range('A', 'Z');
            for ($i = 0; $i < $practiceCount; $i++) {
                $letter = $letters[$i] ?? ('P' . ($i + 1));
                PracticeGroup::create([
                    'theory_group_id' => $theory->id,
                    'code' => 'P1' . $letter,
                    'base_capacity' => $baseCapacity,
                    'teacher_id' => $validated['teacher_id'] ?? null,
                    'schedule' => 'Por definir',
                    'environment' => 'LAB SISTEMAS',
                ]);
            }

            return $course;
        });

        return redirect()->route('courses.show', $course)->with('success', "Asignatura {$course->name} creada exitosamente con {$practiceCount} grupos de práctica.");
    }

    /**
     * Panel detallado del Delegado para un Curso
     */
    public function show(Request $request, Course $course)
    {
        // Consulta base con Eager Loading para evitar el problema N+1
        $query = $course->enrollments()->with(['user', 'practiceGroup.theoryGroup']);

        // Aseguramos cargar las relaciones necesarias en el curso recibido
        $course->load(['practiceGroups.theoryGroup', 'theoryGroups.teacher']);

        // Filtro por búsqueda (nombre o código de usuario)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filtro por Grupo de Teoría
        if ($request->filled('theory_group_id')) {
            $theoryId = $request->input('theory_group_id');
            $query->whereHas('practiceGroup', function ($q) use ($theoryId) {
                $q->where('theory_group_id', $theoryId);
            });
        }

        // Filtro por grupo de práctica
        if ($request->filled('practice_group_id')) {
            $query->where('practice_group_id', $request->input('practice_group_id'));
        }

        // Ordenamiento dinámico
        $sort = $request->input('sort', 'fifo');
        $studentsTable = (new User)->getTable();
        switch ($sort) {
            case 'alphabetical_asc':
                $query->join($studentsTable, 'enrollments.user_id', '=', "{$studentsTable}.id")
                      ->orderBy("{$studentsTable}.name", 'asc')
                      ->select('enrollments.*');
                break;
            case 'alphabetical_desc':
                $query->join($studentsTable, 'enrollments.user_id', '=', "{$studentsTable}.id")
                      ->orderBy("{$studentsTable}.name", 'desc')
                      ->select('enrollments.*');
                break;
            case 'code':
                $query->join($studentsTable, 'enrollments.user_id', '=', "{$studentsTable}.id")
                      ->orderBy("{$studentsTable}.code", 'asc')
                      ->select('enrollments.*');
                break;
            case 'fifo':
            default:
                $query->orderBy('enrolled_at', 'asc');
                break;
        }

        // Paginación limpia de 25 en 25
        $enrollments = $query->paginate(25)->withQueryString();

        // Grupos de práctica con conteo optimizado de aforos y justificados
        $practiceGroups = $course->practiceGroups()
            ->with(['theoryGroup', 'teacher'])
            ->withCount('enrollments')
            ->withCount(['enrollments as laptop_count' => function ($q) {
                $q->where('has_laptop', true);
            }])
            ->withCount(['enrollments as teacher_auth_count' => function ($q) {
                $q->where('teacher_authorized', true);
            }])
            ->withCount(['enrollments as justified_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->where('has_laptop', true)
                        ->orWhere('teacher_authorized', true);
                });
            }])
            ->get();

        $theoryGroups = $course->theoryGroups ?? collect(); 
        $teachers = Teacher::orderBy('name')->get();

        $totalEnrolled = $course->enrollments()->count();
        $totalLaptops = $course->enrollments()->where('has_laptop', true)->count();
        $totalAuthorized = $course->enrollments()->where('teacher_authorized', true)->count();
        $totalReassigned = $course->enrollments()->where('status', 'reasignado')->count();

        // Gráfico Circular de Promociones de este Curso
        $allCourseEnrollments = $course->enrollments()->with('user')->get();
        $coursePromos = [];
        foreach ($allCourseEnrollments as $ce) {
            $promo = DashboardController::extractPromo($ce->user->code ?? null);
            $coursePromos[$promo] = ($coursePromos[$promo] ?? 0) + 1;
        }
        ksort($coursePromos);
        $coursePromoLabels = array_keys($coursePromos);
        $coursePromoData = array_values($coursePromos);

        // Cursos disponibles para el dropdown de navegación rápida
        $allCourses = Course::select('id', 'code_course', 'name')->get();

        return view('courses.show', compact(
            'course',
            'enrollments',
            'practiceGroups',
            'theoryGroups',
            'totalEnrolled',
            'totalLaptops',
            'totalAuthorized',
            'totalReassigned',
            'allCourses',
            'teachers',
            'coursePromoLabels',
            'coursePromoData'
        ));
    }

    /**
     * Actualización de una asignatura
     */
    public function update(Request $request, Course $course, TeacherAssignmentService $teacherService)
    {
        $validated = $request->validate([
            'code_course' => 'required|string|max:50|unique:courses,code_course,' . $course->id,
            'name' => 'required|string|max:255',
            'cycle' => 'required|string|max:50',
            'semester' => 'required|string|max:50',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $t1 = $course->theoryGroups()->first();

        // Validar Regla Oficial UNS
        if (!empty($validated['teacher_id'])) {
            $teacher = Teacher::findOrFail($validated['teacher_id']);
            $check = $teacherService->canAssignTheory($teacher, $course, $t1?->id);
            if (!$check['allowed']) {
                return redirect()->back()->with('error', $check['message']);
            }
        }

        DB::transaction(function () use ($course, $validated, $t1) {
            $course->update([
                'code_course' => strtoupper(trim($validated['code_course'])),
                'name' => strtoupper(trim($validated['name'])),
                'cycle' => $validated['cycle'],
                'semester' => $validated['semester'],
            ]);

            if ($t1) {
                $t1->update(['teacher_id' => $validated['teacher_id'] ?? null]);
            }
        });

        return redirect()->back()->with('success', "Asignatura {$course->name} actualizada correctamente.");
    }

    /**
     * Eliminación de una asignatura
     */
    public function destroy(Course $course)
    {
        $name = $course->name;
        $enrollmentsCount = $course->enrollments()->count();

        if ($enrollmentsCount > 0) {
            return redirect()->back()->with('error', "No se puede eliminar la asignatura '{$name}' porque tiene {$enrollmentsCount} estudiantes matriculados.");
        }

        DB::transaction(function () use ($course) {
            foreach ($course->theoryGroups as $tg) {
                $tg->practiceGroups()->delete();
                $tg->delete();
            }
            $course->delete();
        });

        return redirect()->route('courses.index')->with('success', "La asignatura {$name} fue eliminada exitosamente.");
    }

    /**
     * Dispara el algoritmo oficial de Reorganización y División T1 -> T2
     */
    public function reallocate(Request $request, Course $course, ReallocationService $reallocationService)
    {
        $executor = auth()->user() 
            ?? User::where('role', 'delegado')->first() 
            ?? User::first();

        if (!$executor) {
            $executor = User::create([
                'name' => 'Delegado de Curso',
                'code' => '0202114000',
                'email' => 'delegado@uns.edu.pe',
                'password' => bcrypt('secret'),
                'role' => 'delegado',
            ]);
        }

        $result = $reallocationService->splitTheoryGroups($course, $executor);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Endpoint API para el Simulador Previo de División (Dry Run / Modo Preview)
     */
    public function simulateSplit(Course $course, ReallocationService $reallocationService)
    {
        $simulation = $reallocationService->simulateSplit($course);
        return response()->json($simulation);
    }
}