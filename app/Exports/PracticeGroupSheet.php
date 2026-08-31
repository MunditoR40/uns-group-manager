<?php

namespace App\Exports;

use App\Models\PracticeGroup;
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

class PracticeGroupSheet implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithCustomStartCell,
    WithEvents,
    WithColumnFormatting
{
    protected PracticeGroup $practiceGroup;

    public function __construct(PracticeGroup $practiceGroup)
    {
        $this->practiceGroup = $practiceGroup;
    }

    public function collection(): Collection
    {
        $this->practiceGroup->loadMissing([
            'theoryGroup.course',
            'enrollments.user',
        ]);

        return $this->practiceGroup->enrollments
            ->sortBy('enrolled_at')
            ->values()
            ->map(function ($enrollment, $index) {
                return [
                    $index + 1,
                    $enrollment->user->code,
                    $enrollment->user->name,
                    $this->practiceGroup->theoryGroup->course->name,
                    $this->practiceGroup->theoryGroup->name,
                    $this->practiceGroup->code,
                    $this->practiceGroup->schedule,
                    $enrollment->has_laptop ? 'Sí' : 'No',
                    $enrollment->teacher_authorized ? 'Sí' : 'No',
                    $enrollment->status,
                    $enrollment->enrolled_at->format('d/m/Y H:i:s'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'N°',
            'Código UNS',
            'Apellidos y nombres',
            'Curso',
            'Teoría',
            'Práctica',
            'Horario',
            'Laptop',
            'Autorización docente',
            'Estado',
            'Fecha y hora de matrícula',
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function title(): string
    {
        $courseCode = $this->practiceGroup
            ->theoryGroup
            ->course
            ->code_course;

        return substr(
            $courseCode . '-' . $this->practiceGroup->code,
            0,
            31
        );
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

                $course = $this->practiceGroup
                    ->theoryGroup
                    ->course;

                $theory = $this->practiceGroup
                    ->theoryGroup;

                $total = $this->practiceGroup
                    ->enrollments
                    ->count();

                // Títulos
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue(
                    'A1',
                    'UNIVERSIDAD NACIONAL DEL SANTA'
                );

                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue(
                    'A2',
                    'PADRÓN DE ESTUDIANTES'
                );

                // Información académica
                $sheet->setCellValue('A4', 'Curso:');
                $sheet->setCellValue('B4', $course->name);

                $sheet->setCellValue('A5', 'Código:');
                $sheet->setCellValue('B5', $course->code_course);

                $sheet->setCellValue('D5', 'Semestre:');
                $sheet->setCellValue('E5', $course->semester);

                $sheet->setCellValue('A6', 'Teoría:');
                $sheet->setCellValue('B6', $theory->name);

                $sheet->setCellValue('D6', 'Práctica:');
                $sheet->setCellValue(
                    'E6',
                    $this->practiceGroup->code
                );

                $sheet->setCellValue('A7', 'Horario:');
                $sheet->setCellValue(
                    'B7',
                    $this->practiceGroup->schedule
                );

                $sheet->setCellValue('I7', 'Total:');
                $sheet->setCellValue('J7', $total);

                $sheet->setCellValue('A8', 'Capacidad base:');
                $sheet->setCellValue(
                    'B8',
                    $this->practiceGroup->base_capacity
                );

                // Estilo de títulos
                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle('A2')->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle('A1:K2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Etiquetas de información
                $sheet->getStyle('A4:A8')->getFont()->setBold(true);
                $sheet->getStyle('D5:D6')->getFont()->setBold(true);
                $sheet->getStyle('I7')->getFont()->setBold(true);

                // Encabezados de la tabla
                $sheet->getStyle('A10:K10')->getFont()->setBold(true);

                $sheet->getStyle('A10:K10')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A10:K10')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFE7E6E6');

                // Bordes
                $lastRow = 10 + $total;

                $sheet->getStyle("A10:K{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Centrar columnas específicas
                $sheet->getStyle("A11:B{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("E11:F{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("H11:J{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Evitar que textos largos se salgan
                $sheet->getStyle("A1:K{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Congelar encabezados al desplazarse
                $sheet->freezePane('A11');

                // Filtro
                $sheet->setAutoFilter("A10:K{$lastRow}");

                // Altura de títulos
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(10)->setRowHeight(25);
            },
        ];
    }
}