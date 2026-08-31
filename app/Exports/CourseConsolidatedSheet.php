<?php

namespace App\Exports;

use App\Models\Course;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CourseConsolidatedSheet implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithCustomStartCell,
    WithEvents,
    WithColumnFormatting
{
    protected Course $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function collection(): Collection
    {
        $enrollments = $this->course->enrollments()
            ->with(['user', 'practiceGroup.theoryGroup.teacher'])
            ->orderBy('enrolled_at')
            ->get();

        return $enrollments->map(function ($enrollment, $index) {
            $theory = $enrollment->practiceGroup?->theoryGroup;
            $teacher = $theory?->teacher;

            return [
                $index + 1,
                $enrollment->user->code ?? '-',
                $enrollment->user->name ?? '-',
                $this->course->name,
                $theory?->name ?? '-',
                $teacher?->name ?? 'Por Asignar',
                $enrollment->practiceGroup?->code ?? '-',
                $enrollment->practiceGroup?->schedule ?? '-',
                $enrollment->has_laptop ? 'Sí' : 'No',
                $enrollment->teacher_authorized ? 'Sí' : 'No',
                strtoupper($enrollment->status),
                $enrollment->enrolled_at ? $enrollment->enrolled_at->format('d/m/Y H:i:s') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'N°',
            'Código UNS',
            'Apellidos y Nombres',
            'Curso',
            'Grupo Teoría',
            'Docente Teoría',
            'Grupo Práctica',
            'Horario Práctica',
            'Laptop',
            'Autorización Docente',
            'Estado',
            'Fecha y Hora Matrícula',
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function title(): string
    {
        return 'Consolidado General';
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $total = $this->course->enrollments()->count();

                // Cabecera Institucional
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'UNIVERSIDAD NACIONAL DEL SANTA • SISTEMA SIIGAA');

                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'PADRÓN CONSOLIDADO GENERAL DE MATRÍCULA');

                // Metadatos
                $sheet->setCellValue('A4', 'Curso:');
                $sheet->setCellValue('B4', $this->course->name);

                $sheet->setCellValue('A5', 'Código:');
                $sheet->setCellValue('B5', $this->course->code_course);

                $sheet->setCellValue('E5', 'Ciclo:');
                $sheet->setCellValue('F5', $this->course->cycle ?? 'II Ciclo');

                $sheet->setCellValue('H5', 'Semestre:');
                $sheet->setCellValue('I5', $this->course->semester);

                $sheet->setCellValue('A6', 'Total Inscritos:');
                $sheet->setCellValue('B6', $total);

                // Estilos
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setARGB('FF7F1D1D');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FF1E293B');
                $sheet->getStyle('A1:L2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A4:A6')->getFont()->setBold(true);
                $sheet->getStyle('E5')->getFont()->setBold(true);
                $sheet->getStyle('H5')->getFont()->setBold(true);

                // Encabezados
                $sheet->getStyle('A10:L10')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A10:L10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
                $sheet->getStyle('A10:L10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // Bordes
                $lastRow = 10 + $total;
                $sheet->getStyle("A10:L{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Centrados
                $sheet->getStyle("A11:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E11:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G11:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I11:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->freezePane('A11');
                $sheet->setAutoFilter("A10:L{$lastRow}");

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(10)->setRowHeight(26);
            },
        ];
    }
}