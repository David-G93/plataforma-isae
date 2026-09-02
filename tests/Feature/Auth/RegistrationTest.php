<?php

test('users can register', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});
