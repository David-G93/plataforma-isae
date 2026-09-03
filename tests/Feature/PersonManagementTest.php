<?php

use App\Models\GuardianProfile;
use App\Models\Person;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\InstitutionalRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdminUser(): User
{
    $person = Person::factory()->create();

    $admin = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    return $admin;
}

beforeEach(function () {
    $this->seed(InstitutionalRoleSeeder::class);
});

test('admin can create a person', function () {
    $admin = createAdminUser();

    $response = $this
        ->actingAs($admin)
        ->post(route('people.store'), [
            'dni' => '40123456',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '2012-03-10',
            'email' => 'juan@example.com',
            'phone' => '123456',
            'address' => 'Calle 123',
        ]);

    $person = Person::query()
        ->where('dni', '40123456')
        ->first();

    expect($person)->not->toBeNull();

    $response->assertRedirect(
        route('people.show', $person),
    );
});

test('admin can assign multiple institutional profiles', function () {
    $admin = createAdminUser();

    $person = Person::factory()->create();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'people.institutional-access.update',
                $person,
            ),
            [
                'student' => false,
                'teacher' => true,
                'guardian' => true,
                'is_active' => false,
                'email' => null,
                'password' => null,
            ],
        )
        ->assertRedirect(route('people.show', $person));

    expect($person->fresh()->teacherProfile)
        ->not->toBeNull();

    expect($person->fresh()->guardianProfile)
        ->not->toBeNull();

    expect($person->fresh()->studentProfile)
        ->toBeNull();
});

test('creating platform access synchronizes profile roles', function () {
    $admin = createAdminUser();

    $person = Person::factory()->create([
        'email' => 'responsable@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->put(
            route(
                'people.institutional-access.update',
                $person,
            ),
            [
                'student' => false,
                'teacher' => true,
                'guardian' => true,
                'is_active' => true,
                'email' => 'responsable@example.com',
                'password' => '12345678',
            ],
        );

    $user = $person->fresh()->user;

    expect($user)->not->toBeNull();

    expect($user->hasRole('docente'))->toBeTrue();
    expect($user->hasRole('responsable'))->toBeTrue();
    expect($user->hasRole('alumno'))->toBeFalse();
});

test('administrative roles are preserved when profiles change', function () {
    $admin = createAdminUser();

    $person = Person::factory()->create();

    $user = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    $user->assignRole('director');

    $this
        ->actingAs($admin)
        ->put(
            route(
                'people.institutional-access.update',
                $person,
            ),
            [
                'student' => false,
                'teacher' => true,
                'guardian' => false,
                'is_active' => true,
                'email' => $user->email,
                'password' => '',
            ],
        );

    $user->refresh();

    expect($user->hasRole('director'))->toBeTrue();
    expect($user->hasRole('docente'))->toBeTrue();
});

test('guardian can be linked to several students', function () {
    $admin = createAdminUser();

    $guardianPerson = Person::factory()->create();

    $guardian = GuardianProfile::factory()->create([
        'person_id' => $guardianPerson->id,
    ]);

    $studentOne = StudentProfile::factory()->create();
    $studentTwo = StudentProfile::factory()->create();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'guardian-students.update',
                $guardian,
            ),
            [
                'students' => [
                    [
                        'student_profile_id' => $studentOne->id,
                        'relationship' => 'Madre',
                        'is_primary' => true,
                        'authorized_pickup' => true,
                        'receives_communications' => true,
                    ],
                    [
                        'student_profile_id' => $studentTwo->id,
                        'relationship' => 'Madre',
                        'is_primary' => false,
                        'authorized_pickup' => true,
                        'receives_communications' => true,
                    ],
                ],
            ],
        )
        ->assertRedirect(
            route(
                'people.show',
                $guardianPerson,
            ),
        );

    $guardian->refresh();

    expect($guardian->students)
        ->toHaveCount(2);

    expect(
        $guardian
            ->students
            ->firstWhere('id', $studentOne->id)
            ->pivot
            ->relationship,
    )->toBe('Madre');
});

test('student can have several guardians', function () {
    $student = StudentProfile::factory()->create();

    $guardianOne = GuardianProfile::factory()->create();
    $guardianTwo = GuardianProfile::factory()->create();

    $guardianOne->students()->attach(
        $student->id,
        [
            'relationship' => 'Madre',
            'is_primary' => true,
            'authorized_pickup' => true,
            'receives_communications' => true,
        ],
    );

    $guardianTwo->students()->attach(
        $student->id,
        [
            'relationship' => 'Padre',
            'is_primary' => false,
            'authorized_pickup' => true,
            'receives_communications' => true,
        ],
    );

    expect($student->fresh()->guardians)
        ->toHaveCount(2);
});

test('user without people view permission cannot access people', function () {
    $person = Person::factory()->create();

    $user = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    $user->assignRole('docente');

    $this
        ->actingAs($user)
        ->get(route('people.index'))
        ->assertForbidden();
});

test('user with people view permission can access people', function () {
    $person = Person::factory()->create();

    $user = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    $user->assignRole('preceptor');

    $this
        ->actingAs($user)
        ->get(route('people.index'))
        ->assertOk();
});

test('user with people view but without manage cannot create people', function () {
    $person = Person::factory()->create();

    $user = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    $user->assignRole('preceptor');

    $this
        ->actingAs($user)
        ->get(route('people.create'))
        ->assertForbidden();
});

test('secretary can manage people', function () {
    $person = Person::factory()->create();

    $user = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    $user->assignRole('secretario');

    $this
        ->actingAs($user)
        ->get(route('people.create'))
        ->assertOk();
});