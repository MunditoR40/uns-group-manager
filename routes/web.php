<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/matriculados', [EnrollmentController::class, 'index'])->name('enrollments.index');
Route::patch('/enrollments/{enrollment}/toggle', [EnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle');
Route::get('/auditoria', [AuditController::class, 'index'])->name('audit.index');

Route::get('/export/excel', [ExportController::class, 'exportExcel'])->name('export.excel');
Route::get('/export/pdf', [ExportController::class, 'exportPdf'])->name('export.pdf');