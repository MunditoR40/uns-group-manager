<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'practice_group_id',
        'has_laptop',
        'teacher_authorized',
        'enrolled_at',
        'status',
    ];

    protected $casts = [
        'has_laptop' => 'boolean',
        'teacher_authorized' => 'boolean',
        'enrolled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function practiceGroup()
    {
        return $this->belongsTo(PracticeGroup::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
