<?php

use App\Models\User;
use Database\Seeders\InstitutionalRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(InstitutionalRoleSeeder::class);
});

test('guest cannot access academic module', function () {
    $this->get(
        route('academic.index'),
    )->assertRedirect(
        route('login'),
    );
});

test('user without academic view permission cannot access academic module', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(
            route('academic.index'),
        )
        ->assertForbidden();
});

test('teacher can access academic module', function () {
    $user = User::factory()->create();

    $user->assignRole('docente');

    $this->actingAs($user)
        ->get(
            route('academic.index'),
        )
        ->assertOk();
});

test('preceptor can access academic module', function () {
    $user = User::factory()->create();

    $user->assignRole('preceptor');

    $this->actingAs($user)
        ->get(
            route('academic.index'),
        )
        ->assertOk();
});

test('secretary can access academic module', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $this->actingAs($user)
        ->get(
            route('academic.index'),
        )
        ->assertOk();
});

test('admin can access academic module', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(
            route('academic.index'),
        )
        ->assertOk();
});