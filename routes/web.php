<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('students')->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('students.index');
    Route::get('/{code}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/{code}', [StudentController::class, 'update'])->name('students.update');
});