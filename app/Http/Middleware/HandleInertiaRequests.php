<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user) {
            $user->loadMissing('person');
        }

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,

                    'person' => $user->person ? [
                        'id' => $user->person->id,
                        'dni' => $user->person->dni,
                        'first_name' => $user->person->first_name,
                        'last_name' => $user->person->last_name,
                        'full_name' => $user->person->full_name,
                    ] : null,

                    'roles' => $user->getRoleNames()->values()->all(),
                ] : null,
            ],
        ];
    }
}