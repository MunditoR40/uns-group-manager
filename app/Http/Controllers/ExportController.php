<?php

namespace App\Http\Controllers;

use App\Exports\CourseExcelExport;
use App\Exports\EnrollmentsExport;
use App\Models\Course;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Exportacion general del sistema (Para Delegado General o Administrador)
     */
    public function excel()
    {
        return Excel::download(
            new EnrollmentsExport(),
            'matriculas_generales_uns.xlsx'
        );
    }

    /**
     * Exportacion exclusiva por Curso o por Teoria (Para Delegado de Curso / Docente)
     */
    public function courseExcel(Request $request, Course $course)
    {
        $theoryGroupId = $request->input('theory_group_id');
        $cleanCode = str_replace([' ', '/', '\\', '-'], '_', $course->code_course);

        if ($theoryGroupId) {
            $theory = TheoryGroup::where('course_id', $course->id)->where('id', $theoryGroupId)->first();
            $theorySlug = $theory ? str_replace(' ', '_', strtolower($theory->name)) : 'teoria';
            $fileName = "padron_{$cleanCode}_{$theorySlug}.xlsx";
        } else {
            $fileName = "padron_completo_{$cleanCode}.xlsx";
        }

        return Excel::download(
            new CourseExcelExport($course, $theoryGroupId ? (int)$theoryGroupId : null),
            $fileName
        );
    }

    public function pdf(PracticeGroup $practiceGroup)
    {
        $practiceGroup->load([
            'theoryGroup.course',
            'enrollments.user',
        ]);

        $enrollments = $practiceGroup->enrollments
            ->sortBy('enrolled_at')
            ->values();

        $pdf = Pdf::loadView('exports.attendance_pdf', [
            'practiceGroup' => $practiceGroup,
            'enrollments' => $enrollments,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download(
            'acta_' . $practiceGroup->code . '.pdf'
        );
    }
}