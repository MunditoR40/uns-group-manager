<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

// Pantalla de inicio redirige al Catálogo de Cursos
Route::get('/', function () {
    return redirect()->route('courses.index');
});

// Panel y Gestión de Cursos
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/courses/{course}/reallocate', [CourseController::class, 'reallocate'])->name('courses.reallocate');

// Acciones sobre Matrículas de Estudiantes
Route::patch('/enrollments/{enrollment}/toggle', [EnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle');
Route::post('/enrollments/{enrollment}/move-group', [EnrollmentController::class, 'moveGroup'])->name('enrollments.move-group');

// Exportaciones Oficiales PDF y Excel
Route::get('/exports/enrollments/excel', [ExportController::class, 'excel'])
    ->name('exports.enrollments.excel');
Route::get('/exports/practice-groups/{practiceGroup}/pdf', [ExportController::class, 'pdf'])
    ->name('exports.practice-groups.pdf');

// Bitácora de Auditoría y Rollback
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::post('/audit/rollback/{batchId}', [AuditController::class, 'rollback'])->name('audit.rollback');
Route::post('/audit/single/{auditLog}/rollback', [AuditController::class, 'rollbackSingle'])->name('audit.rollback-single');
