<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', [StudentController::class, 'index'])->name('students.index');
Route::get('/courses', [StudentController::class, 'courses'])->name('courses.index');
Route::get('/create-sample-data', [StudentController::class, 'createSampleData'])->name('create.sample');

// Enrollment routes
Route::post('/enroll', [StudentController::class, 'enroll'])->name('student.enroll');
Route::delete('/drop/{student}/{course}', [StudentController::class, 'drop'])->name('student.drop');