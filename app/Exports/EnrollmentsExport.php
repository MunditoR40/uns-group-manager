<?php

namespace App\Exports;

use App\Models\PracticeGroup;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EnrollmentsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];

        $practiceGroups = PracticeGroup::with('theoryGroup.course')
            ->get();

        foreach ($practiceGroups as $practiceGroup) {
            $sheets[] = new PracticeGroupSheet($practiceGroup);
        }

        return $sheets;
    }
}