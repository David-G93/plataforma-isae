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
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Personas - administración
    |--------------------------------------------------------------------------
    |
    | Las rutas estáticas como /people/create deben declararse antes de
    | /people/{person}, para que "create" no sea interpretado como un ID.
    |
    */

    Route::middleware('permission:people.manage')->group(function () {
        Route::get('/people/create', [PersonController::class, 'create'])
            ->name('people.create');

        Route::post('/people', [PersonController::class, 'store'])
            ->name('people.store');

        Route::get('/people/{person}/edit', [PersonController::class, 'edit'])
            ->name('people.edit');

        Route::put('/people/{person}', [PersonController::class, 'update'])
            ->name('people.update');

        Route::patch('/people/{person}', [PersonController::class, 'update']);

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
        Route::get('/people', [PersonController::class, 'index'])
            ->name('people.index');

        Route::get('/people/{person}', [PersonController::class, 'show'])
            ->name('people.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Perfil personal
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';