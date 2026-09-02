<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('users can update their password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('password.edit'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
