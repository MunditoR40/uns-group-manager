<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Pantalla de inicio redirige al Catálogo de Cursos
Route::get('/', function () {
    return redirect()->route('courses.index');
});

// Dashboard de Analítica y Estadísticas UNS (Chart.js)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// CRUD y Gestión de Cursos (Entregable ampliado de Jared)
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
Route::post('/courses/{course}/reallocate', [CourseController::class, 'reallocate'])->name('courses.reallocate');
Route::get('/courses/{course}/simulate-split', [CourseController::class, 'simulateSplit'])->name('courses.simulate-split');

// CRUD y Gestión de la Plana Docente UNS (Con Regla UNS de Teorías)
Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

// Acciones sobre Matrículas y Estudiantes
Route::patch('/enrollments/{enrollment}/toggle', [EnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle');
Route::post('/enrollments/{enrollment}/move-group', [EnrollmentController::class, 'moveGroup'])->name('enrollments.move-group');
Route::post('/students/{user}/toggle-delegate', [StudentController::class, 'toggleDelegate'])->name('students.toggle-delegate');

// Exportaciones Oficiales PDF y Excel
Route::get('/exports/enrollments/excel', [ExportController::class, 'excel'])
    ->name('exports.enrollments.excel');
Route::get('/courses/{course}/excel', [ExportController::class, 'courseExcel'])
    ->name('courses.excel');
Route::get('/exports/practice-groups/{practiceGroup}/pdf', [ExportController::class, 'pdf'])
    ->name('exports.practice-groups.pdf');

// Bitácora de Auditoría y Rollback
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::post('/audit/rollback/{batchId}', [AuditController::class, 'rollback'])->name('audit.rollback');
Route::post('/audit/single/{auditLog}/rollback', [AuditController::class, 'rollbackSingle'])->name('audit.rollback-single');