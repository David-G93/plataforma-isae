<?php

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('academic year can be created', function () {
    $academicYear = AcademicYear::factory()->create([
        'year' => 2026,
        'name' => 'Ciclo Lectivo 2026',
        'starts_at' => '2026-03-02',
        'ends_at' => '2026-12-18',
        'is_active' => true,
    ]);

    expect($academicYear->year)
        ->toBe(2026);

    expect($academicYear->is_active)
        ->toBeTrue();
});

test('academic year year must be unique', function () {
    AcademicYear::factory()->create([
        'year' => 2026,
    ]);

    expect(
        fn () => AcademicYear::factory()->create([
            'year' => 2026,
        ]),
    )->toThrow(QueryException::class);
});

test('level has several grades', function () {
    $level = Level::factory()->create([
        'name' => 'Nivel Secundario',
        'code' => 'secundario',
    ]);

    Grade::factory()->count(3)->create([
        'level_id' => $level->id,
    ]);

    expect($level->fresh()->grades)
        ->toHaveCount(3);
});

test('grade belongs to a level', function () {
    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    expect($grade->level->id)
        ->toBe($level->id);
});

test('course belongs to academic year and grade', function () {
    $academicYear = AcademicYear::factory()->create();

    $grade = Grade::factory()->create();

    $course = Course::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
    ]);

    expect($course->academicYear->id)
        ->toBe($academicYear->id);

    expect($course->grade->id)
        ->toBe($grade->id);
});

test('course has several divisions', function () {
    $course = Course::factory()->create();

    Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'A',
    ]);

    Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'B',
    ]);

    expect($course->fresh()->divisions)
        ->toHaveCount(2);
});

test('division belongs to a course', function () {
    $course = Course::factory()->create();

    $division = Division::factory()->create([
        'course_id' => $course->id,
    ]);

    expect($division->course->id)
        ->toBe($course->id);
});

test('same division name cannot be duplicated inside same course', function () {
    $course = Course::factory()->create();

    Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'A',
    ]);

    expect(
        fn () => Division::factory()->create([
            'course_id' => $course->id,
            'name' => 'A',
        ]),
    )->toThrow(QueryException::class);
});

test('same division name can exist in different courses', function () {
    $courseOne = Course::factory()->create();

    $courseTwo = Course::factory()->create();

    Division::factory()->create([
        'course_id' => $courseOne->id,
        'name' => 'A',
    ]);

    Division::factory()->create([
        'course_id' => $courseTwo->id,
        'name' => 'A',
    ]);

    expect(
        Division::query()
            ->where('name', 'A')
            ->count(),
    )->toBe(2);
});

test('modality can exist independently', function () {
    $modality = Modality::factory()->create([
        'name' => 'Economía y Administración',
        'code' => 'economia-administracion',
    ]);

    expect($modality->name)
        ->toBe('Economía y Administración');

    expect($modality->is_active)
        ->toBeTrue();
});

test('academic boolean fields are correctly cast', function () {
    $level = Level::factory()->create([
        'is_active' => true,
    ]);

    $course = Course::factory()->create([
        'is_active' => false,
    ]);

    expect($level->is_active)
        ->toBeTrue();

    expect($course->is_active)
        ->toBeFalse();
});