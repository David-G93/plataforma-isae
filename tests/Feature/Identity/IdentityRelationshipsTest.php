<?php

use App\Models\GuardianProfile;
use App\Models\Person;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('allows a person to exist without a user account', function (): void {
    $person = Person::create([
        'dni' => '30111222',
        'first_name' => 'Laura',
        'last_name' => 'Gomez',
    ]);

    expect($person->user)->toBeNull();
});

it('allows a user account to belong to a person', function (): void {
    $person = Person::create([
        'dni' => '30111223',
        'first_name' => 'Martin',
        'last_name' => 'Perez',
    ]);

    $user = User::factory()->create([
        'person_id' => $person->id,
        'is_active' => true,
    ]);

    expect($user->person)
        ->not->toBeNull()
        ->id->toBe($person->id);

    expect($person->fresh()->user)
        ->not->toBeNull()
        ->id->toBe($user->id);
});

it('allows one person to have multiple institutional profiles', function (): void {
    $person = Person::create([
        'dni' => '30111224',
        'first_name' => 'Andrea',
        'last_name' => 'Lopez',
    ]);

    $teacher = TeacherProfile::create([
        'person_id' => $person->id,
    ]);

    $guardian = GuardianProfile::create([
        'person_id' => $person->id,
    ]);

    expect($person->fresh()->teacherProfile->id)->toBe($teacher->id);
    expect($person->fresh()->guardianProfile->id)->toBe($guardian->id);
});

it('allows a person to have a student profile', function (): void {
    $person = Person::create([
        'dni' => '50111225',
        'first_name' => 'Sofia',
        'last_name' => 'Martinez',
    ]);

    $student = StudentProfile::create([
        'person_id' => $person->id,
    ]);

    expect($student->person->id)->toBe($person->id);
    expect($person->fresh()->studentProfile->id)->toBe($student->id);
});

it('does not allow duplicate dni values', function (): void {
    Person::create([
        'dni' => '30111226',
        'first_name' => 'Juan',
        'last_name' => 'Diaz',
    ]);

    Person::create([
        'dni' => '30111226',
        'first_name' => 'Pedro',
        'last_name' => 'Diaz',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('does not allow duplicate profiles of the same type for one person', function (): void {
    $person = Person::create([
        'dni' => '30111227',
        'first_name' => 'Carlos',
        'last_name' => 'Ruiz',
    ]);

    StudentProfile::create([
        'person_id' => $person->id,
    ]);

    StudentProfile::create([
        'person_id' => $person->id,
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('casts user active status to boolean', function (): void {
    $user = User::factory()->create([
        'is_active' => 1,
    ]);

    expect($user->is_active)->toBeTrue();
});