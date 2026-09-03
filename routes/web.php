<?php

use App\Http\Controllers\GuardianStudentController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::put(
        '/people/{person}/institutional-access',
        [PersonController::class, 'updateInstitutionalAccess'],
    )->name('people.institutional-access.update');

    Route::put(
        '/guardian-profiles/{guardianProfile}/students',
        [GuardianStudentController::class, 'update'],
    )->name('guardian-students.update');

    Route::resource('people', PersonController::class)
        ->except('destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';