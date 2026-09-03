<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public registration screen is disabled', function () {
    $this->get('/register')
        ->assertNotFound();
});

test('public registration endpoint is disabled', function () {
    $this->post('/register', [
        'name' => 'Usuario',
        'email' => 'usuario@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertGuest();
});