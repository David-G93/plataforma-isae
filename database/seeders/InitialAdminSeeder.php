<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $email = 'admin@isae.local';
            $dni = '12345678';

            // Elimina solamente una persona huérfana creada por un intento previo.
            Person::query()
                ->where('dni', $dni)
                ->whereDoesntHave('user')
                ->delete();

            $user = User::query()
                ->where('email', $email)
                ->first();

            if ($user && $user->person) {
                $person = $user->person;

                $person->update([
                    'dni' => $dni,
                    'first_name' => 'Administrador',
                    'last_name' => 'ISAE',
                    'email' => $email,
                ]);
            } else {
                $person = Person::updateOrCreate(
                    ['dni' => $dni],
                    [
                        'first_name' => 'Administrador',
                        'last_name' => 'ISAE',
                        'email' => $email,
                    ],
                );
            }

            if ($user) {
                $user->update([
                    'person_id' => $person->id,
                    'name' => $person->full_name,
                    'password' => Hash::make('12345678'),
                    'is_active' => true,
                ]);
            } else {
                $user = User::create([
                    'person_id' => $person->id,
                    'name' => $person->full_name,
                    'email' => $email,
                    'password' => Hash::make('12345678'),
                    'is_active' => true,
                ]);
            }

            $user->syncRoles(['admin']);
        });
    }
}