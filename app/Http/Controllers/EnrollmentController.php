<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\User;
use App\Services\ReallocationService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Alterna en tiempo real (AJAX) el estado de laptop o autorización docente
     * utilizando ReallocationService para garantizar auditoría inmutable.
     */
    public function toggleStatus(Request $request, Enrollment $enrollment, ReallocationService $reallocationService)
    {
        $field = $request->input('field');
        $value = $request->boolean('value');

        if (!in_array($field, ['has_laptop', 'teacher_authorized'])) {
            return response()->json(['success' => false, 'message' => 'Campo no válido'], 400);
        }

        $executor = auth()->user() 
            ?? User::where('role', 'delegado')->first() 
            ?? User::first();

        if (!$executor) {
            $executor = User::create([
                'name' => 'Delegado de Curso',
                'code' => '0202114000',
                'email' => 'delegado@uns.edu.pe',
                'password' => bcrypt('secret'),
                'role' => 'delegado',
            ]);
        }

        try {
            if ($field === 'has_laptop') {
                $reallocationService->toggleLaptop($enrollment, $value, $executor);
            } else {
                $reallocationService->toggleTeacherAuth($enrollment, $value, $executor);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado y auditado correctamente',
                'field' => $field,
                'value' => $value,
                'student_name' => $enrollment->user->name ?? 'Estudiante',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reasigna manualmente a un alumno de grupo de práctica.
     */
    public function moveGroup(Request $request, Enrollment $enrollment, ReallocationService $reallocationService)
    {
        $request->validate([
            'new_practice_group_id' => 'required|exists:practice_groups,id',
        ]);

        $newGroup = PracticeGroup::findOrFail($request->input('new_practice_group_id'));

        $executor = auth()->user() 
            ?? User::where('role', 'delegado')->first() 
            ?? User::first();

        if (!$executor) {
            $executor = User::create([
                'name' => 'Delegado de Curso',
                'code' => '0202114000',
                'email' => 'delegado@uns.edu.pe',
                'password' => bcrypt('secret'),
                'role' => 'delegado',
            ]);
        }

        $reallocationService->moveStudentManually($enrollment, $newGroup, $executor);

        return redirect()->back()->with('success', "El estudiante {$enrollment->user->name} fue reasignado exitosamente al grupo {$newGroup->code}.");
    }
}
