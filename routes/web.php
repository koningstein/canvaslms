<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OldResultController;
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

//Route::middleware('auth')->get('/modules/select', function () {
//    $selectedCourses = session('selected_courses', []);
//    return view('modules.select', compact('selectedCourses'));
//})->name('modules.select');
//
//Route::middleware('auth')->get('/students/select', function () {
//    $selectedCourses = session('selected_courses', []);
//    return view('students.select', compact('selectedCourses'));
//})->name('students.select');Route::middleware('auth')->get('/students/select', function () {
//    $selectedCourses = session('selected_courses', []);
//    return view('students.select', compact('selectedCourses'));
//})->name('students.select');
//Route::middleware('auth')->get('/results/progress', [ResultController::class, 'getSelectedProgress'])->name('results.progress');

// Multi-step wizard routes
Route::middleware('auth')->group(function () {
    // Stap 1: Modules selecteren
    Route::get('/modules/select', function () {
        $selectedCourses = session('selected_courses', []);
        return view('modules.select', compact('selectedCourses'));
    })->name('modules.select');

    // Stap 2: Assignment Groups selecteren
    Route::get('/assignment-groups/select', function () {
        $selectedCourses = session('selected_courses', []);
        $selectedModules = session('selected_modules', []);
        return view('assignment-groups.select', compact('selectedCourses', 'selectedModules'));
    })->name('assignment-groups.select');

    // Stap 3: Students selecteren
    Route::get('/students/select', function () {
        $selectedCourses = session('selected_courses', []);
        $selectedModules = session('selected_modules', []);
        $selectedAssignmentGroups = session('selected_assignment_groups', []);
        return view('students.select', compact('selectedCourses', 'selectedModules', 'selectedAssignmentGroups'));
    })->name('students.select');

    // Stap 4: Result opties kiezen
    Route::get('/results/select', function () {
        $selectedCourses = session('selected_courses', []);
        $selectedModules = session('selected_modules', []);
        $selectedAssignmentGroups = session('selected_assignment_groups', []);
        $selectedUsers = session('selected_users', []);
        return view('results.select', compact('selectedCourses', 'selectedModules', 'selectedAssignmentGroups', 'selectedUsers'));
    })->name('results.select');

    // Stap 5: Resultaten tonen
    Route::get('/results/progress', [OldResultController::class, 'getSelectedProgress'])->name('results.progress');
});

require __DIR__.'/auth.php';
