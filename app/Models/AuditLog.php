<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'batch_id',
        'enrollment_id',
        'user_id',
        'action_type',
        'previous_state',
        'new_state',
        'description',
        'is_reverted',
    ];

    protected $casts = [
        'previous_state' => 'array',
        'new_state' => 'array',
        'is_reverted' => 'boolean',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
