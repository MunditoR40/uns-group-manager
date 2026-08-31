<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::with(['theoryGroups.practiceGroups'])->get();

        $selectedCourseId = $request->input('course_id', $courses->first()?->id);
        $course = $courses->firstWhere('id', $selectedCourseId) ?? $courses->first();
        $selectedCourse = $course;

        $totalEnrolled = 0;
        $laptopCount = 0;
        $reassignedCount = 0;
        $groupsCount = 0;
        $practiceGroups = collect();

        if ($course) {
            $totalEnrolled = Enrollment::where('course_id', $course->id)->count();
            $laptopCount = Enrollment::where('course_id', $course->id)->where('has_laptop', true)->count();
            $reassignedCount = Enrollment::where('course_id', $course->id)->where('status', 'reasignado')->count();

            // Consulta con conteo de justificados únicos (Laptop O Permiso)
            $practiceGroups = PracticeGroup::whereHas('theoryGroup', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                ->with('theoryGroup')
                ->withCount(['enrollments' => function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                }])
                ->withCount(['enrollments as laptop_count' => function ($query) use ($course) {
                    $query->where('course_id', $course->id)->where('has_laptop', true);
                }])
                ->withCount(['enrollments as teacher_auth_count' => function ($query) use ($course) {
                    $query->where('course_id', $course->id)->where('teacher_authorized', true);
                }])
                ->withCount(['enrollments as justified_count' => function ($query) use ($course) {
                    // Contar al alumno una sola vez si tiene laptop o permiso
                    $query->where('course_id', $course->id)
                          ->where(function ($sub) {
                              $sub->where('has_laptop', true)
                                  ->orWhere('teacher_authorized', true);
                          });
                }])
                ->get();

            $groupsCount = $practiceGroups->count();
        }

        return view('delegado.dashboard', compact(
            'courses',
            'course',
            'selectedCourse',
            'totalEnrolled',
            'laptopCount',
            'reassignedCount',
            'groupsCount',
            'practiceGroups'
        ));
    }
}
