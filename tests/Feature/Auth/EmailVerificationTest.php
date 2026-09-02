<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect('/dashboard?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
