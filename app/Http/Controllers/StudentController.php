<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Alterna o asigna el rol de delegado a un estudiante.
     * Permite cambios de delegado a futuro sin modificar el nombre de la persona.
     */
    public function toggleDelegate(User $user)
    {
        $newRole = $user->role === 'delegado' ? 'estudiante' : 'delegado';
        $user->update(['role' => $newRole]);

        $statusMessage = $newRole === 'delegado'
            ? "El estudiante {$user->name} fue designado oficialmente como Delegado."
            : "Se retiró la designación de delegado a {$user->name}.";

        return redirect()->back()->with('success', $statusMessage);
    }
}