<?php

use App\Models\GuardianProfile;
use App\Models\Person;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a person to exist without a user account', function () {
    $person = Person::factory()->create();

    expect($person->user)->toBeNull();
});

it('allows a person to have a student profile', function () {
    $person = Person::factory()->create();

    $profile = StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    expect($profile->person->is($person))->toBeTrue()
        ->and($person->studentProfile->is($profile))->toBeTrue();
});

it('allows a person to be both teacher and guardian', function () {
    $person = Person::factory()->create();

    $teacherProfile = TeacherProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    $guardianProfile = GuardianProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    expect($person->teacherProfile->is($teacherProfile))->toBeTrue()
        ->and($person->guardianProfile->is($guardianProfile))->toBeTrue();
});

it('does not allow duplicate dni', function () {
    Person::factory()->create([
        'dni' => '30123456',
    ]);

    expect(fn () => Person::factory()->create([
        'dni' => '30123456',
    ]))->toThrow(QueryException::class);
});

it('does not allow two users for the same person', function () {
    $person = Person::factory()->create();

    User::factory()->create([
        'person_id' => $person->id,
    ]);

    expect(fn () => User::factory()->create([
        'person_id' => $person->id,
    ]))->toThrow(QueryException::class);
});

it('does not allow two student profiles for the same person', function () {
    $person = Person::factory()->create();

    StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    expect(fn () => StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]))->toThrow(QueryException::class);
});

it('casts is active as boolean', function () {
    $user = User::factory()->create([
        'is_active' => 1,
    ]);

    expect($user->is_active)->toBeTrue()
        ->and($user->is_active)->toBeBool();
});