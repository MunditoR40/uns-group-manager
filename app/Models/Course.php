<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code_course', 'name', 'semester'];

    public function theoryGroups()
    {
        return $this->hasMany(TheoryGroup::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
