<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheoryGroup extends Model
{
    protected $fillable = ['course_id', 'name'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function practiceGroups()
    {
        return $this->hasMany(PracticeGroup::class);
    }
}