<?php

use App\Models\User;

test('users can update their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'New Name', 'email' => 'new@example.com'])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->name)->toBe('New Name')
        ->and($user->fresh()->email_verified_at)->toBeNull();
});
