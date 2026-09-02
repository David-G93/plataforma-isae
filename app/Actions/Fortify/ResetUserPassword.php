<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    /**
     * Reset a user password for Laravel Fortify's reset flow.
     *
     * @param  array<string, mixed>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => ['required', 'confirmed', Password::defaults()],
        ])->validate();

        $user->forceFill([
            'password' => $input['password'],
            'remember_token' => Str::random(60),
        ])->save();
    }
}
