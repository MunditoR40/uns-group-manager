<?php
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/exports/enrollments/excel', [ExportController::class, 'excel'])
    ->name('exports.enrollments.excel');
Route::get(
    '/exports/practice-groups/{practiceGroup}/pdf',
    [ExportController::class, 'pdf']
)->name('exports.practice-groups.pdf');