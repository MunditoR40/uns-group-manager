<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\TheoryGroup;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CourseExcelExport implements WithMultipleSheets
{
    protected Course $course;
    protected ?int $theoryGroupId;

    public function __construct(Course $course, ?int $theoryGroupId = null)
    {
        $this->course = $course;
        $this->theoryGroupId = $theoryGroupId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Si se pide una teoría específica
        if ($this->theoryGroupId) {
            $theory = TheoryGroup::where('course_id', $this->course->id)
                ->where('id', $this->theoryGroupId)
                ->firstOrFail();

            $sheets[] = new TheoryGroupSheet($theory);
            return $sheets;
        }

        // 1. Hoja Consolidada del Curso
        $sheets[] = new CourseConsolidatedSheet($this->course);

        // 2. Hojas individuales por cada Teoría (Teoría 1, Teoría 2 si existe)
        $this->course->loadMissing(['theoryGroups.teacher', 'practiceGroups']);
        foreach ($this->course->theoryGroups as $theory) {
            $sheets[] = new TheoryGroupSheet($theory);
        }

        // 3. Hojas individuales por cada Grupo de Práctica (Laboratorio)
        foreach ($this->course->practiceGroups as $practice) {
            $sheets[] = new PracticeGroupSheet($practice);
        }

        return $sheets;
    }
}