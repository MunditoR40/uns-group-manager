<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::post('/audit/rollback/{batchId}', [AuditController::class, 'rollback'])->name('audit.rollback');