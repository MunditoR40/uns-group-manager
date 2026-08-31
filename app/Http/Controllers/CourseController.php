<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\User;
use App\Services\ReallocationService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Catálogo interactivo de Cursos (Dashboard inicial del sistema)
     */
    public function index()
    {
        $courses = Course::with(['theoryGroups.practiceGroups'])
            ->withCount('enrollments')
            ->get();

        $stats = [
            'total_students' => Enrollment::distinct('user_id')->count('user_id'),
            'total_enrollments' => Enrollment::count(),
            'total_laptops' => Enrollment::where('has_laptop', true)->count(),
            'total_authorized' => Enrollment::where('teacher_authorized', true)->count(),
            'total_reassigned' => Enrollment::where('status', 'reasignado')->count(),
        ];

        return view('courses.index', compact('courses', 'stats'));
    }

    /**
     * Panel detallado del Delegado para un Curso
     */
    public function show(Request $request, Course $course)
    {
        // Consulta base con Eager Loading para evitar el problema N+1
        $query = $course->enrollments()->with(['user', 'practiceGroup.theoryGroup']);

        // Aseguramos cargar las relaciones necesarias en el curso recibido
        $course->load(['practiceGroups.theoryGroup', 'theoryGroups']);

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

        // Ordenamiento
        $sort = $request->input('sort', 'fifo');
        switch ($sort) {
            case 'alphabetical_asc':
                $query->join('users', 'enrollments.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc')
                      ->select('enrollments.*');
                break;
            case 'alphabetical_desc':
                $query->join('users', 'enrollments.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'desc')
                      ->select('enrollments.*');
                break;
            case 'code':
                $query->join('users', 'enrollments.user_id', '=', 'users.id')
                      ->orderBy('users.code', 'asc')
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
            ->with('theoryGroup')
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

        $totalEnrolled = $course->enrollments()->count();
        $totalLaptops = $course->enrollments()->where('has_laptop', true)->count();
        $totalAuthorized = $course->enrollments()->where('teacher_authorized', true)->count();
        $totalReassigned = $course->enrollments()->where('status', 'reasignado')->count();

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
            'allCourses'
        ));
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
}
