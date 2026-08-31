<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'department',
        'condition',
    ];

    public function theoryGroups()
    {
        return $this->hasMany(TheoryGroup::class);
    }

    public function practiceGroups()
    {
        return $this->hasMany(PracticeGroup::class);
    }

    /**
     * Cuenta cuantas teorias tiene asignadas en un ciclo determinado
     */
    public function theoriesCountInCycle(string $cycle): int
    {
        return $this->theoryGroups()
            ->whereHas('course', function ($q) use ($cycle) {
                $q->where('cycle', $cycle);
            })
            ->count();
    }
}