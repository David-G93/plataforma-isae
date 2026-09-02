<?php

use App\Models\User;

test('guests are redirected to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('verified users can visit the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('dashboard'));
});
