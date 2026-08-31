<?php

namespace App\Services;

use App\Models\Course;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\TheoryGroup;

class TeacherAssignmentService
{
    /**
     * Valida la regla institucional de la UNS:
     * Un docente no puede dictar 2 teorias en el mismo ciclo academico.
     */
    public function canAssignTheory(Teacher $teacher, Course $course, ?int $ignoreTheoryGroupId = null): array
    {
        $existingTheory = TheoryGroup::where('teacher_id', $teacher->id)
            ->whereHas('course', function ($q) use ($course) {
                $q->where('cycle', $course->cycle);
            })
            ->when($ignoreTheoryGroupId, fn($q) => $q->where('id', '!=', $ignoreTheoryGroupId))
            ->with('course')
            ->first();

        if ($existingTheory) {
            return [
                'allowed' => false,
                'message' => "Restricción Oficial UNS: El docente {$teacher->name} ya tiene asignada la {$existingTheory->name} del curso '{$existingTheory->course->name}' en el {$course->cycle}. Según el reglamento de la UNS, un docente no puede tener 2 teorías en un solo ciclo académico.",
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Asignación de teoría válida y autorizada.',
        ];
    }

    /**
     * Regla UNS para Practicas:
     * Un docente si puede tener varias practicas con el motivo de llenar su carga lectiva.
     */
    public function canAssignPractice(Teacher $teacher, PracticeGroup $practiceGroup): array
    {
        return [
            'allowed' => true,
            'message' => 'Asignación permitida para completar la carga lectiva docente.',
        ];
    }
}