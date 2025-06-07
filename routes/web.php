<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('page.home');;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Course routes - alleen voor ingelogde gebruikers
Route::middleware('auth')->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
});

Route::middleware('auth')->get('/modules/select', function () {
    $selectedCourses = session('selected_courses', []);
    return view('modules.select', compact('selectedCourses'));
})->name('modules.select');

Route::middleware('auth')->get('/students/select', function () {
    $selectedCourses = session('selected_courses', []);
    return view('students.select', compact('selectedCourses'));
})->name('students.select');Route::middleware('auth')->get('/students/select', function () {
    $selectedCourses = session('selected_courses', []);
    return view('students.select', compact('selectedCourses'));
})->name('students.select');
Route::middleware('auth')->get('/results/progress', [ResultController::class, 'getSelectedProgress'])->name('results.progress');

require __DIR__.'/auth.php';
