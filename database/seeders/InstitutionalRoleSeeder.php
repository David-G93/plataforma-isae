<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstitutionalRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'admin',
            'gestion',
            'director',
            'preceptor',
            'docente',
            'alumno',
            'responsable',
        ] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
