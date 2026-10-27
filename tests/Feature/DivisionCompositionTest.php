<?php

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Person;
use App\Models\TeacherProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDivisionCompositionDivision(): Division
{
    $year = AcademicYear::factory()->create(['year' => 2026]);
    $level = Level::factory()->create(['code' => 'secundario']);
    $grade = Grade::factory()->create([
        'level_id' => $level->id,
        'code' => 'secundario-2',
    ]);
    $course = Course::factory()->create([
        'academic_year_id' => $year->id,
        'grade_id' => $grade->id,
    ]);

    return Division::factory()->create([
        'course_id' => $course->id,
    ]);
}

test('division can have several available teachers', function () {
    $division = createDivisionCompositionDivision();

    $teachers = TeacherProfile::factory()->count(2)->create();
    $division->teachers()->sync($teachers->pluck('id'));

    expect($division->fresh()->teachers)
        ->toHaveCount(2);
});

test('division can have staff with assignment role', function () {
    $division = createDivisionCompositionDivision();
    $person = Person::factory()->create();

    $division->staff()->attach($person->id, [
        'assignment_role' => 'preceptor',
    ]);

    $staff = $division->fresh()->staff->first();

    expect($staff->id)->toBe($person->id)
        ->and($staff->pivot->assignment_role)->toBe('preceptor');
});
