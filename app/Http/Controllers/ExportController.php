<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $courseId = $request->input('course_id');
        $course = Course::find($courseId) ?? Course::first();

        if (!$course) {
            return redirect()->back();
        }

        $enrollments = Enrollment::with(['user', 'practiceGroup.theoryGroup'])
            ->where('course_id', $course->id)
            ->orderBy('enrolled_at', 'asc')
            ->get();

        $cleanCourseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $course->name);
        $fileName = "Matriculados_{$cleanCourseName}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$fileName}\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return new StreamedResponse(function () use ($enrollments, $course) {
            $handle = fopen('php://output', 'w');
            // BOM para compatibilidad con Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['UNIVERSIDAD NACIONAL DEL SANTA']);
            fputcsv($handle, ['PADRON OFICIAL DE MATRICULADOS']);
            fputcsv($handle, ['CURSO: ' . $course->name . ' (' . $course->code_course . ')', 'SEMESTRE: ' . $course->semester]);
            fputcsv($handle, []);
            fputcsv($handle, ['N°', 'Codigo UNS', 'Apellidos y Nombres', 'Teoria', 'Grupo Practica', 'Fecha y Hora Matricula', 'Tiene Laptop', 'Permiso Docente', 'Estado']);

            foreach ($enrollments as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row->user->code ?? 'S/C',
                    $row->user->name,
                    $row->practiceGroup->theoryGroup->name ?? 'Teoría 1',
                    $row->practiceGroup->code ?? '---',
                    $row->enrolled_at,
                    $row->has_laptop ? 'SI' : 'NO',
                    $row->teacher_authorized ? 'SI' : 'NO',
                    strtoupper($row->status)
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $courseId = $request->input('course_id');
        $course = Course::find($courseId) ?? Course::first();

        $enrollments = Enrollment::with(['user', 'practiceGroup.theoryGroup'])
            ->where('course_id', $course?->id)
            ->orderBy('enrolled_at', 'asc')
            ->get();

        return view('delegado.reports.pdf', compact('course', 'enrollments'));
    }
}