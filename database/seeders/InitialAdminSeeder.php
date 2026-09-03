<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $person = Person::updateOrCreate(
            [
                'dni' => '30123456',
            ],
            [
                'first_name' => 'Administrador',
                'last_name' => 'ISAE',
                'email' => 'admin@isae.local',
            ],
        );

        $user = User::updateOrCreate(
            [
                'person_id' => $person->id,
            ],
            [
                'name' => $person->full_name,
                'email' => 'admin@isae.local',
                'password' => Hash::make('Admin123!'),
                'is_active' => true,
            ],
        );

        $user->syncRoles(['admin']);
    }
}