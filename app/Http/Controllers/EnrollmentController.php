<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::all();
        
        $selectedCourseId = $request->input('course_id', $courses->first()?->id);
        $course = $courses->firstWhere('id', $selectedCourseId) ?? $courses->first();

        $query = Enrollment::with(['user', 'practiceGroup.theoryGroup'])
            ->where('course_id', $course?->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('practice_group')) {
            $groupCode = $request->input('practice_group');
            $query->whereHas('practiceGroup', function ($q) use ($groupCode) {
                $q->where('code', $groupCode);
            });
        }

        switch ($request->input('sort', 'fifo')) {
            case 'name_asc':
                $query->join('users', 'users.id', '=', 'enrollments.user_id')
                      ->orderBy('users.name', 'asc')
                      ->select('enrollments.*');
                break;
            case 'name_desc':
                $query->join('users', 'users.id', '=', 'enrollments.user_id')
                      ->orderBy('users.name', 'desc')
                      ->select('enrollments.*');
                break;
            case 'code_asc':
                $query->join('users', 'users.id', '=', 'enrollments.user_id')
                      ->orderBy('users.code', 'asc')
                      ->select('enrollments.*');
                break;
            default:
                $query->orderBy('enrolled_at', 'asc');
                break;
        }

        $enrollments = $query->paginate(15)->withQueryString();

        $groups = PracticeGroup::whereHas('theoryGroup', function ($q) use ($course) {
            $q->where('course_id', $course?->id);
        })->with('theoryGroup')->get();

        return view('delegado.students.index', compact('enrollments', 'courses', 'course', 'groups'));
    }

    public function toggleStatus(Request $request, Enrollment $enrollment)
    {
        $field = $request->input('field');
        $value = $request->boolean('value');

        if (!in_array($field, ['has_laptop', 'teacher_authorized'])) {
            return response()->json(['success' => false, 'message' => 'Campo inválido'], 400);
        }

        try {
            $enrollment->$field = $value;
            $enrollment->save();

            try {
                AuditLog::create([
                    'user_id' => $enrollment->user_id,
                    'action_type' => $field === 'has_laptop' ? 'laptop_toggle' : 'auth_toggle',
                    'description' => "Se actualizó el parámetro {$field} a " . ($value ? 'SÍ' : 'NO') . " para {$enrollment->user->name}.",
                ]);
            } catch (\Throwable $th) {
                // Log opcional no bloqueante
            }

            return response()->json([
                'success' => true,
                'message' => 'Guardado exitosamente'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}