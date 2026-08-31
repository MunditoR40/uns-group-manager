<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->get();
        return view('students.index', compact('students'));
    }

    public function edit($code)
    {
        // Busca de forma flexible para evitar el error 'Column not found'
        $student = User::where('id', $code)
            ->orWhere('email', 'like', "%{$code}%")
            ->firstOrFail();

        $courses = Course::all();

        return view('students.edit', compact('student', 'courses', 'code'));
    }

    public function update(Request $request, $code)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,delegate',
        ]);

        $student = User::where('id', $code)->firstOrFail();

        $student->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->route('students.index')->with('success', 'Estudiante actualizado.');
    }
}