<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Padrón general de estudiantes registrados
     */
    public function index(Request $request)
    {
        $query = User::withCount('enrollments');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && in_array($request->role, ['estudiante', 'delegado'])) {
            $query->where('role', $request->role);
        }

        $students = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::count(),
            'delegates' => User::where('role', 'delegado')->count(),
            'students' => User::where('role', 'estudiante')->count(),
        ];

        return view('students.index', compact('students', 'stats'));
    }

    /**
     * Formulario de edición de datos del estudiante (Revisado y adaptado de Jared)
     */
    public function edit($id)
    {
        $student = User::where('id', $id)
            ->orWhere('code', $id)
            ->firstOrFail();

        $allCourses = Course::with('theoryGroups')->orderBy('name')->get();
        $enrolledCourseIds = $student->enrollments()->pluck('course_id')->toArray();

        return view('students.edit', compact('student', 'allCourses', 'enrolledCourseIds'));
    }

    /**
     * Actualización de datos del estudiante
     */
    public function update(Request $request, $id)
    {
        $student = User::where('id', $id)
            ->orWhere('code', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:students,code,' . $student->id,
            'email' => 'required|email|max:255|unique:students,email,' . $student->id,
            'role' => 'required|in:estudiante,delegado',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', "Datos del estudiante {$student->name} actualizados exitosamente.");
    }

    /**
     * Alterna o asigna el rol de delegado a un estudiante.
     * Permite cambios de delegado a futuro sin modificar el nombre de la persona.
     */
    public function toggleDelegate(User $user)
    {
        $newRole = $user->role === 'delegado' ? 'estudiante' : 'delegado';
        $user->update(['role' => $newRole]);

        $statusMessage = $newRole === 'delegado'
            ? "El estudiante {$user->name} fue designado oficialmente como Delegado."
            : "Se retiró la designación de delegado a {$user->name}.";

        return redirect()->back()->with('success', $statusMessage);
    }
}