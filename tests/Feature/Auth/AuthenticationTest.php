<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get(route('login'))->assertOk();
});

test('users can authenticate and logout', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
});
