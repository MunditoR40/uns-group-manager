<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticeGroup extends Model
{
    protected $fillable = ['theory_group_id', 'code', 'base_capacity', 'schedule'];

    public function theoryGroup()
    {
        return $this->belongsTo(TheoryGroup::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
