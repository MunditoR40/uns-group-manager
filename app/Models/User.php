<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'code', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
