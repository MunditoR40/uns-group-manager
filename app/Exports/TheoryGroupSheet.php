<?php

namespace App\Exports;

use App\Models\Enrollment;
use App\Models\TheoryGroup;
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

class TheoryGroupSheet implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithCustomStartCell,
    WithEvents,
    WithColumnFormatting
{
    protected TheoryGroup $theoryGroup;

    public function __construct(TheoryGroup $theoryGroup)
    {
        $this->theoryGroup = $theoryGroup;
    }

    public function collection(): Collection
    {
        $this->theoryGroup->loadMissing(['course', 'teacher', 'practiceGroups']);

        $enrollments = Enrollment::whereHas('practiceGroup', function ($q) {
            $q->where('theory_group_id', $this->theoryGroup->id);
        })
        ->with(['user', 'practiceGroup'])
        ->orderBy('enrolled_at')
        ->get();

        return $enrollments->map(function ($enrollment, $index) {
            return [
                $index + 1,
                $enrollment->user->code ?? '-',
                $enrollment->user->name ?? '-',
                $this->theoryGroup->course->name,
                $this->theoryGroup->name,
                $this->theoryGroup->teacher->name ?? 'Docente por asignar',
                $enrollment->practiceGroup->code ?? '-',
                $enrollment->practiceGroup->schedule ?? '-',
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
            'Docente Responsable',
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
        return substr($this->theoryGroup->name . ' - ' . $this->theoryGroup->course->code_course, 0, 31);
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
                $course = $this->theoryGroup->course;
                $teacherName = $this->theoryGroup->teacher->name ?? 'Por Asignar';

                $total = Enrollment::whereHas('practiceGroup', function ($q) {
                    $q->where('theory_group_id', $this->theoryGroup->id);
                })->count();

                // Cabecera Institucional
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'UNIVERSIDAD NACIONAL DEL SANTA • FACULTAD DE INGENIERÍA');

                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'PADRÓN OFICIAL DE TEORÍA: ' . strtoupper($this->theoryGroup->name));

                // Metadatos Académicos
                $sheet->setCellValue('A4', 'Curso:');
                $sheet->setCellValue('B4', $course->name);

                $sheet->setCellValue('A5', 'Código:');
                $sheet->setCellValue('B5', $course->code_course);

                $sheet->setCellValue('E5', 'Ciclo:');
                $sheet->setCellValue('F5', $course->cycle ?? 'II Ciclo');

                $sheet->setCellValue('H5', 'Semestre:');
                $sheet->setCellValue('I5', $course->semester);

                $sheet->setCellValue('A6', 'Docente:');
                $sheet->setCellValue('B6', $teacherName);

                $sheet->setCellValue('E6', 'Teoría:');
                $sheet->setCellValue('F6', $this->theoryGroup->name);

                $sheet->setCellValue('H6', 'Total Alumnos:');
                $sheet->setCellValue('I6', $total);

                // Estilos de Título
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setARGB('FF7F1D1D');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FF1E293B');
                $sheet->getStyle('A1:L2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Estilos de Metadatos
                $sheet->getStyle('A4:A6')->getFont()->setBold(true);
                $sheet->getStyle('E5:E6')->getFont()->setBold(true);
                $sheet->getStyle('H5:H6')->getFont()->setBold(true);

                // Encabezados de Tabla
                $sheet->getStyle('A10:L10')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A10:L10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF991B1B');
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