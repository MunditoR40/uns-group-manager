<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
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

        $practiceGroups = $course->practiceGroups()->with('theoryGroup')->withCount('enrollments')->get();
        $theoryGroups = $course->theoryGroups ?? collect(); 

        return view('courses.show', compact('course', 'enrollments', 'practiceGroups', 'theoryGroups'));
    }
}