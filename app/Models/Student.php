<?php

namespace App\Models;

/**
 * Modelo Student que mapea formalmente a la tabla 'students'.
 * Hereda de User para compatibilidad de autenticacion y roles.
 */
class Student extends User
{
    protected $table = 'students';
}