<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'students';

    protected $fillable = ['name', 'code', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Helpers de rol
     */
    public function isDelegate(): bool
    {
        return $this->role === 'delegado';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'docente';
    }

    public function isStudent(): bool
    {
        return $this->role === 'estudiante';
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
