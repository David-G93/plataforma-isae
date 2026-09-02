<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('seeds the institutional base roles', function (): void {
    $this->seed(RolePermissionSeeder::class);

    expect(\Spatie\Permission\Models\Role::pluck('name')->all())
        ->toContain(
            'admin',
            'gestion',
            'director',
            'preceptor',
            'docente',
            'alumno',
            'responsable',
        );
});

it('allows a user to have multiple roles', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();

    $user->assignRole(['docente', 'responsable']);

    expect($user->hasRole('docente'))->toBeTrue();
    expect($user->hasRole('responsable'))->toBeTrue();
});

it('allows permissions to be assigned through roles', function (): void {
    $this->seed(RolePermissionSeeder::class);

    Permission::create([
        'name' => 'users.view',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $role = \Spatie\Permission\Models\Role::findByName('gestion');

    $role->givePermissionTo('users.view');
    $user->assignRole($role);

    expect($user->can('users.view'))->toBeTrue();
});