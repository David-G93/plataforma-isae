<?php

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using dni and password', function () {
    $person = Person::factory()->create([
        'dni' => '30123456',
    ]);

    $user = User::factory()->create([
        'person_id' => $person->id,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'dni' => '30123456',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $person = Person::factory()->create([
        'dni' => '30123456',
    ]);

    User::factory()->create([
        'person_id' => $person->id,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $this->post('/login', [
        'dni' => '30123456',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('inactive users can not authenticate', function () {
    $person = Person::factory()->create([
        'dni' => '30123456',
    ]);

    User::factory()->create([
        'person_id' => $person->id,
        'password' => Hash::make('password'),
        'is_active' => false,
    ]);

    $this->post('/login', [
        'dni' => '30123456',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('person without user account can not authenticate', function () {
    Person::factory()->create([
        'dni' => '30123456',
    ]);

    $this->post('/login', [
        'dni' => '30123456',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('unknown dni can not authenticate', function () {
    $this->post('/login', [
        'dni' => '99999999',
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();

    $response->assertRedirect('/');
});