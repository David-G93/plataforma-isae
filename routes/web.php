<?php

use App\Http\Controllers\AcademicController;
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
})
    ->middleware([
        'auth',
        'verified',
    ])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Personas - administración
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:people.manage')->group(function () {
        Route::get(
            '/people/create',
            [PersonController::class, 'create'],
        )->name('people.create');

        Route::post(
            '/people',
            [PersonController::class, 'store'],
        )->name('people.store');

        Route::get(
            '/people/{person}/edit',
            [PersonController::class, 'edit'],
        )->name('people.edit');

        Route::put(
            '/people/{person}',
            [PersonController::class, 'update'],
        )->name('people.update');

        Route::patch(
            '/people/{person}',
            [PersonController::class, 'update'],
        );

        Route::put(
            '/people/{person}/institutional-access',
            [PersonController::class, 'updateInstitutionalAccess'],
        )->name('people.institutional-access.update');

        Route::put(
            '/guardian-profiles/{guardianProfile}/students',
            [GuardianStudentController::class, 'update'],
        )->name('guardian-students.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Personas - lectura
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:people.view')->group(function () {
        Route::get(
            '/people',
            [PersonController::class, 'index'],
        )->name('people.index');

        Route::get(
            '/people/{person}',
            [PersonController::class, 'show'],
        )->name('people.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Académico - administración
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:academic.manage')->group(function () {
        Route::post(
            '/academic/years',
            [AcademicController::class, 'storeAcademicYear'],
        )->name('academic.years.store');

        Route::post(
            '/academic/courses',
            [AcademicController::class, 'storeCourse'],
        )->name('academic.courses.store');

        Route::post(
            '/academic/divisions',
            [AcademicController::class, 'storeDivision'],
        )->name('academic.divisions.store');

        Route::post(
            '/academic/subjects',
            [AcademicController::class, 'storeSubject'],
        )->name('academic.subjects.store');

        Route::post(
            '/academic/study-plans',
            [AcademicController::class, 'storeStudyPlan'],
        )->name('academic.study-plans.store');

        Route::post(
            '/academic/study-plan-subjects',
            [AcademicController::class, 'storeStudyPlanSubject'],
        )->name('academic.study-plan-subjects.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Académico - lectura
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:academic.view')->group(function () {
        Route::get(
            '/academic',
            [AcademicController::class, 'index'],
        )->name('academic.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Perfil personal
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit'],
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update'],
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy'],
    )->name('profile.destroy');
});

require __DIR__.'/auth.php';