<?php

namespace App\Http\Controllers;

use App\Exports\EnrollmentsExport;
use App\Models\PracticeGroup;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function excel()
    {
        return Excel::download(
            new EnrollmentsExport(),
            'matriculas_uns.xlsx'
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