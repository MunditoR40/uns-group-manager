<?php
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AuditController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/exports/enrollments/excel', [ExportController::class, 'excel'])
    ->name('exports.enrollments.excel');
Route::get(
    '/exports/practice-groups/{practiceGroup}/pdf',
    [ExportController::class, 'pdf']
)->name('exports.practice-groups.pdf');

Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::post('/audit/rollback/{batchId}', [AuditController::class, 'rollback'])->name('audit.rollback');
Route::post('/audit/single/{auditLog}/rollback', [AuditController::class, 'rollbackSingle'])->name('audit.rollback-single');


