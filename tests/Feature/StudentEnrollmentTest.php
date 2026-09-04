<?php

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Modality;
use App\Models\Person;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use Database\Seeders\AcademicStructureSeeder;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->seed(AcademicStructureSeeder::class);
});

it('academic structure seeder creates primary and secondary structure', function () {
    expect(
        AcademicYear::query()
            ->where('year', 2026)
            ->exists(),
    )->toBeTrue();

    expect(
        \App\Models\Level::query()
            ->where('code', 'primario')
            ->exists(),
    )->toBeTrue();

    expect(
        \App\Models\Level::query()
            ->where('code', 'secundario')
            ->exists(),
    )->toBeTrue();

    expect(
        \App\Models\Grade::query()
            ->where('code', 'primario-1')
            ->exists(),
    )->toBeTrue();

    expect(
        \App\Models\Grade::query()
            ->where('code', 'secundario-5')
            ->exists(),
    )->toBeTrue();
});

it('academic structure seeder creates institutional modalities', function () {
    expect(
        Modality::query()
            ->where('code', 'bioinformatica')
            ->where('name', 'Bioinformática')
            ->exists(),
    )->toBeTrue();

    expect(
        Modality::query()
            ->where('code', 'ciencias-naturales')
            ->where('name', 'Ciencias Naturales')
            ->exists(),
    )->toBeTrue();

    expect(
        Modality::query()
            ->where('code', 'informatica')
            ->where('name', 'Informática')
            ->exists(),
    )->toBeTrue();
});

it('student can have annual enrollment without modality', function () {
    $person = Person::factory()->create();

    $student = StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    $academicYear = AcademicYear::factory()->create();

    $division = Division::factory()->create();

    $enrollment = StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'division_id' => $division->id,
        'modality_id' => null,
    ]);

    expect($enrollment->studentProfile->is($student))->toBeTrue();
    expect($enrollment->academicYear->is($academicYear))->toBeTrue();
    expect($enrollment->division->is($division))->toBeTrue();
    expect($enrollment->modality)->toBeNull();
});

it('secondary student enrollment can have modality', function () {
    $person = Person::factory()->create();

    $student = StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    $academicYear = AcademicYear::factory()->create();

    $division = Division::factory()->create();

    $modality = Modality::factory()->create();

    $enrollment = StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'division_id' => $division->id,
        'modality_id' => $modality->id,
    ]);

    expect($enrollment->modality->is($modality))->toBeTrue();
});

it('student cannot have two enrollments in same academic year', function () {
    $person = Person::factory()->create();

    $student = StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    $academicYear = AcademicYear::factory()->create();

    $firstDivision = Division::factory()->create();
    $secondDivision = Division::factory()->create();

    StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'division_id' => $firstDivision->id,
    ]);

    expect(fn () => StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'division_id' => $secondDivision->id,
    ]))->toThrow(QueryException::class);
});

it('same student can have enrollment in different academic years', function () {
    $person = Person::factory()->create();

    $student = StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    $firstAcademicYear = AcademicYear::factory()->create();
    $secondAcademicYear = AcademicYear::factory()->create();

    $firstDivision = Division::factory()->create();
    $secondDivision = Division::factory()->create();

    StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $firstAcademicYear->id,
        'division_id' => $firstDivision->id,
    ]);

    StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $secondAcademicYear->id,
        'division_id' => $secondDivision->id,
    ]);

    expect(
        StudentEnrollment::query()
            ->where('student_profile_id', $student->id)
            ->count(),
    )->toBe(2);
});