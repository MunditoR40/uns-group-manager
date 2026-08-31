<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Extrae el año de promocion de un codigo institucional UNS (ej: 0202614001 -> Promo 2026)
     */
    public static function extractPromo(?string $code): string
    {
        if (!$code) {
            return 'Sin Código';
        }
        if (preg_match('/(20\d{2})/', $code, $matches)) {
            return 'Promo ' . $matches[1];
        }
        return 'Otras Promociones';
    }

    public function index(Request $request)
    {
        $allCourses = Course::with(['theoryGroups.teacher', 'practiceGroups'])->get();
        $selectedCourseId = $request->input('course_id');
        $selectedCourse = $selectedCourseId ? Course::find($selectedCourseId) : null;

        // Base query para enrollments segun el filtro
        $enrollmentsQuery = Enrollment::with(['user', 'practiceGroup.theoryGroup.course']);
        if ($selectedCourse) {
            $enrollmentsQuery->where('course_id', $selectedCourse->id);
        }
        $enrollments = $enrollmentsQuery->get();

        // 1. KPIs Globales
        $totalStudents = Enrollment::when($selectedCourse, fn($q) => $q->where('course_id', $selectedCourse->id))
            ->distinct('user_id')
            ->count('user_id');

        $totalEnrollments = $enrollments->count();
        $totalLaptops = $enrollments->where('has_laptop', true)->count();
        $totalAuthorized = $enrollments->where('teacher_authorized', true)->count();
        $totalReassigned = $enrollments->where('status', 'reasignado')->count();

        $laptopPercentage = $totalEnrollments > 0 ? round(($totalLaptops / $totalEnrollments) * 100, 1) : 0;
        $reassignedPercentage = $totalEnrollments > 0 ? round(($totalReassigned / $totalEnrollments) * 100, 1) : 0;

        // 2. Gráfico: Distribución de Matriculados por Promoción (Promo Principal vs Repitentes/Otras)
        $promosCount = [];
        foreach ($enrollments as $e) {
            $promo = self::extractPromo($e->user->code ?? null);
            $promosCount[$promo] = ($promosCount[$promo] ?? 0) + 1;
        }
        ksort($promosCount);

        $promoLabels = array_keys($promosCount);
        $promoData = array_values($promosCount);

        // 3. Gráfico: Aforo de Grupos de Práctica (Inscritos vs Capacidad Base)
        $practiceGroupsQuery = PracticeGroup::with(['theoryGroup.course'])
            ->withCount('enrollments')
            ->withCount(['enrollments as justified_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->where('has_laptop', true)->orWhere('teacher_authorized', true);
                });
            }]);

        if ($selectedCourse) {
            $practiceGroupsQuery->whereHas('theoryGroup', fn($q) => $q->where('course_id', $selectedCourse->id));
        }

        $practiceGroups = $practiceGroupsQuery->get();

        $practiceLabels = [];
        $practiceEnrolled = [];
        $practiceCapacity = [];
        $practiceEffective = [];
        $practiceColors = [];

        foreach ($practiceGroups as $pg) {
            $label = ($selectedCourse ? '' : ($pg->theoryGroup->course->code_course . ' - ')) . $pg->code;
            $baseCap = (int)($pg->base_capacity ?: 15);
            $effCap = $baseCap + $pg->justified_count;
            $enrolled = $pg->enrollments_count;

            $practiceLabels[] = $label;
            $practiceEnrolled[] = $enrolled;
            $practiceCapacity[] = $baseCap;
            $practiceEffective[] = $effCap;

            // Color condicional: rojo si sobrecupo, ambar si lleno, azul si normal
            if ($enrolled > $effCap) {
                $practiceColors[] = 'rgba(220, 38, 38, 0.85)'; // Red
            } elseif ($enrolled >= $baseCap) {
                $practiceColors[] = 'rgba(217, 119, 6, 0.85)'; // Amber
            } else {
                $practiceColors[] = 'rgba(37, 99, 235, 0.85)'; // Blue
            }
        }

        // Conteo de grupos con sobrecupo activo
        $criticalOvercapacityGroups = $practiceGroups->filter(fn($pg) => $pg->enrollments_count > ((int)($pg->base_capacity ?: 15) + $pg->justified_count))->count();

        // 4. Gráfico: Alumnos con Laptop vs Sin Laptop
        $noLaptopCount = $totalEnrollments - $totalLaptops;
        $laptopChart = [
            'labels' => ['Con Laptop Propia', 'Sin Laptop (Requiere PC Lab)'],
            'data' => [$totalLaptops, $noLaptopCount],
        ];

        // 5. Gráfico: Carga Lectiva Docente (Teorías y Prácticas)
        $teachers = Teacher::withCount(['theoryGroups', 'practiceGroups'])->get();
        $teacherLabels = [];
        $teacherTheories = [];
        $teacherPractices = [];

        foreach ($teachers as $t) {
            $shortName = explode(' ', $t->name);
            $displayName = ($shortName[0] ?? '') . ' ' . ($shortName[2] ?? ($shortName[1] ?? ''));
            $teacherLabels[] = $displayName;
            $teacherTheories[] = $t->theory_groups_count;
            $teacherPractices[] = $t->practice_groups_count;
        }

        // 6. Gráfico: Matrículas por Ciclo Académico
        $cyclesCount = Course::withCount('enrollments')
            ->get()
            ->groupBy('cycle')
            ->map(fn($group) => $group->sum('enrollments_count'))
            ->toArray();

        return view('dashboard', compact(
            'allCourses',
            'selectedCourse',
            'totalStudents',
            'totalEnrollments',
            'totalLaptops',
            'totalAuthorized',
            'totalReassigned',
            'laptopPercentage',
            'reassignedPercentage',
            'criticalOvercapacityGroups',
            'promoLabels',
            'promoData',
            'practiceLabels',
            'practiceEnrolled',
            'practiceCapacity',
            'practiceEffective',
            'practiceColors',
            'laptopChart',
            'teacherLabels',
            'teacherTheories',
            'teacherPractices',
            'cyclesCount'
        ));
    }
}