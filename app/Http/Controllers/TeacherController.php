<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Listado y panel de administracion de la Plana Docente UNS
     */
    public function index()
    {
        $teachers = Teacher::with(['theoryGroups.course', 'practiceGroups.theoryGroup.course'])
            ->withCount(['theoryGroups', 'practiceGroups'])
            ->orderBy('name')
            ->get();

        return view('teachers.index', compact('teachers'));
    }

    /**
     * Registro de un nuevo docente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:teachers,code',
            'email' => 'required|email|unique:teachers,email',
            'department' => 'required|string|max:255',
            'condition' => 'required|string|max:100',
        ]);

        $teacher = Teacher::create($validated);

        return redirect()->back()->with('success', "Docente {$teacher->name} registrado exitosamente.");
    }

    /**
     * Actualizacion de datos del docente
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:teachers,code,' . $teacher->id,
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'department' => 'required|string|max:255',
            'condition' => 'required|string|max:100',
        ]);

        $teacher->update($validated);

        return redirect()->back()->with('success', "Datos del docente {$teacher->name} actualizados correctamente.");
    }

    /**
     * Eliminacion de docente
     */
    public function destroy(Teacher $teacher)
    {
        $theoriesCount = $teacher->theoryGroups()->count();
        $practicesCount = $teacher->practiceGroups()->count();

        if ($theoriesCount > 0 || $practicesCount > 0) {
            return redirect()->back()->with('error', "No se puede eliminar al docente {$teacher->name} porque tiene {$theoriesCount} teoría(s) y {$practicesCount} práctica(s) asignadas activas.");
        }

        $name = $teacher->name;
        $teacher->delete();

        return redirect()->back()->with('success', "El docente {$name} fue eliminado del sistema.");
    }
}