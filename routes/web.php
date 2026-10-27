<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AcademicQualityController;
use App\Http\Controllers\GuardianStudentController;
use App\Http\Controllers\DivisionManagementController;
use App\Http\Controllers\OfficialAttendanceController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeachingController;
use App\Http\Controllers\TeachingAttendanceController;
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

        Route::put(
            '/academic/divisions/{division}/composition',
            [DivisionManagementController::class, 'update'],
        )->name('academic.divisions.composition.update');

        Route::post(
            '/academic/teachings/generate',
            [TeachingController::class, 'generate'],
        )->name('academic.teachings.generate');

        Route::put(
            '/academic/teachings/{teaching}/teachers',
            [TeachingController::class, 'updateTeachers'],
        )->name('academic.teachings.teachers.update');
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

        Route::get(
            '/academic/divisions/manage',
            [DivisionManagementController::class, 'index'],
        )->name('academic.divisions.manage');

        Route::get(
            '/academic/teachings',
            [TeachingController::class, 'index'],
        )->name('academic.teachings.index');

        Route::get(
            '/academic/quality',
            [AcademicQualityController::class, 'index'],
        )->name('academic.quality.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Asistencia
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:attendance.view')->group(function () {
        Route::get(
            '/attendance/teachings',
            [TeachingAttendanceController::class, 'index'],
        )->name('attendance.teachings.index');

        Route::get(
            '/attendance/teachings/{teaching}',
            [TeachingAttendanceController::class, 'show'],
        )->name('attendance.teachings.show');

        Route::get(
            '/attendance/official',
            [OfficialAttendanceController::class, 'index'],
        )->name('attendance.official.index');
    });

    Route::middleware('permission:attendance.manage')->group(function () {
        Route::put(
            '/attendance/teachings/{teaching}',
            [TeachingAttendanceController::class, 'update'],
        )->name('attendance.teachings.update');

        Route::put(
            '/attendance/official/{division}/{period}',
            [OfficialAttendanceController::class, 'update'],
        )->name('attendance.official.update');
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