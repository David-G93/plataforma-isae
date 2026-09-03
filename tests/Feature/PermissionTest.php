<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('assigns a role to a user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);

    $user->assignRole($role);

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

it('assigns multiple roles to a user', function () {
    $user = User::factory()->create();
    $gestion = Role::create(['name' => 'gestion']);
    $docente = Role::create(['name' => 'docente']);

    $user->assignRole($gestion, $docente);

    expect($user->fresh()->hasAllRoles(['gestion', 'docente']))->toBeTrue();
});

it('grants a permission through a role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'director']);
    $permission = Permission::create(['name' => 'ver panel']);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->fresh()->can('ver panel'))->toBeTrue();
});
