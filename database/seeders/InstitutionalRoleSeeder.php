<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstitutionalRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'people.view',
            'people.manage',

            'academic.view',
            'academic.manage',

            'attendance.view',
            'attendance.manage',

            'grades.view',
            'grades.manage',

            'communications.view',
            'communications.manage',

            'calendar.view',
            'calendar.manage',

            'reports.view',
            'reports.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'admin',
            'gestion',
            'rector',
            'director',
            'vicedirector',
            'secretario',
            'preceptor',
            'docente',
            'alumno',
            'responsable',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        /*
        |--------------------------------------------------------------------------
        | Administración
        |--------------------------------------------------------------------------
        */

        Role::findByName('admin', 'web')
            ->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Gestión general
        |--------------------------------------------------------------------------
        */

        Role::findByName('gestion', 'web')
            ->syncPermissions($permissions);

        Role::findByName('rector', 'web')
            ->syncPermissions($permissions);

        Role::findByName('director', 'web')
            ->syncPermissions($permissions);

        Role::findByName('vicedirector', 'web')
            ->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Secretaría
        |--------------------------------------------------------------------------
        */

        Role::findByName('secretario', 'web')
            ->syncPermissions([
                'people.view',
                'people.manage',

                'academic.view',
                'academic.manage',

                'attendance.view',

                'grades.view',

                'communications.view',
                'communications.manage',

                'calendar.view',
                'calendar.manage',

                'reports.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Preceptoría
        |--------------------------------------------------------------------------
        */

        Role::findByName('preceptor', 'web')
            ->syncPermissions([
                'people.view',

                'academic.view',

                'attendance.view',
                'attendance.manage',

                'grades.view',

                'communications.view',
                'communications.manage',

                'calendar.view',

                'reports.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Docentes
        |--------------------------------------------------------------------------
        */

        Role::findByName('docente', 'web')
            ->syncPermissions([
                'academic.view',

                'attendance.view',
                'attendance.manage',

                'grades.view',
                'grades.manage',

                'communications.view',
                'communications.manage',

                'calendar.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Estudiantes
        |--------------------------------------------------------------------------
        */

        Role::findByName('alumno', 'web')
            ->syncPermissions([
                'academic.view',
                'grades.view',
                'communications.view',
                'calendar.view',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Responsables
        |--------------------------------------------------------------------------
        */

        Role::findByName('responsable', 'web')
            ->syncPermissions([
                'academic.view',
                'grades.view',
                'communications.view',
                'calendar.view',
            ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}