<?php

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\Person;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\Teaching;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('subject can have teachings', function () {
    $subject = Subject::factory()->create([
        'name' => 'Matemática',
        'code' => 'matematica',
    ]);

    Teaching::factory()->count(2)->create([
        'subject_id' => $subject->id,
    ]);

    expect($subject->fresh()->teachings)
        ->toHaveCount(2);
});

test('teaching belongs to subject and division', function () {
    $division = createTeachingTestDivision();

    $subject = Subject::factory()->create([
        'name' => 'Lengua',
        'code' => 'lengua',
    ]);

    $teaching = Teaching::factory()->create([
        'subject_id' => $subject->id,
        'division_id' => $division->id,
    ]);

    expect($teaching->subject->id)
        ->toBe($subject->id);

    expect($teaching->division->id)
        ->toBe($division->id);
});

test('teaching can have several teachers', function () {
    $teaching = Teaching::factory()->create();

    $teacherOne = createTeachingTestTeacher();
    $teacherTwo = createTeachingTestTeacher();

    $teaching->teachers()->attach([
        $teacherOne->id,
        $teacherTwo->id,
    ]);

    expect($teaching->fresh()->teachers)
        ->toHaveCount(2);
});

test('teacher can have several teachings', function () {
    $teacher = createTeachingTestTeacher();

    $teachingOne = Teaching::factory()->create();
    $teachingTwo = Teaching::factory()->create();

    $teacher->teachings()->attach([
        $teachingOne->id,
        $teachingTwo->id,
    ]);

    expect($teacher->fresh()->teachings)
        ->toHaveCount(2);
});

test('general teaching includes every active student in division', function () {
    $context = createTeachingEnrollmentContext();

    $naturales = Modality::factory()->create([
        'name' => 'Ciencias Naturales',
        'code' => 'naturales',
    ]);

    $informatica = Modality::factory()->create([
        'name' => 'Informática',
        'code' => 'informatica',
    ]);

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        $naturales,
    );

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        $informatica,
    );

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        $naturales,
    );

    $teaching = Teaching::factory()->create([
        'subject_id' => Subject::factory()->create([
            'name' => 'Matemática',
            'code' => 'matematica',
        ])->id,

        'division_id' => $context['division']->id,
        'modality_id' => null,
    ]);

    expect(
        $teaching
            ->eligibleEnrollments()
            ->count(),
    )->toBe(3);
});

test('modality teaching only includes students from that modality', function () {
    $context = createTeachingEnrollmentContext();

    $naturales = Modality::factory()->create([
        'name' => 'Ciencias Naturales',
        'code' => 'naturales',
    ]);

    $informatica = Modality::factory()->create([
        'name' => 'Informática',
        'code' => 'informatica',
    ]);

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        $naturales,
    );

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        $naturales,
    );

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        $informatica,
    );

    $teaching = Teaching::factory()->create([
        'subject_id' => Subject::factory()->create([
            'name' => 'Biología específica',
            'code' => 'biologia-especifica',
        ])->id,

        'division_id' => $context['division']->id,
        'modality_id' => $naturales->id,
    ]);

    expect(
        $teaching
            ->eligibleEnrollments()
            ->count(),
    )->toBe(2);

    expect(
        $teaching
            ->eligibleEnrollments()
            ->where(
                'modality_id',
                $informatica->id,
            )
            ->exists(),
    )->toBeFalse();
});

test('inactive student enrollment is excluded from teaching list', function () {
    $context = createTeachingEnrollmentContext();

    $activeStudent = createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
    );

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
        null,
        'inactive',
    );

    $teaching = Teaching::factory()->create([
        'division_id' => $context['division']->id,
        'modality_id' => null,
    ]);

    $eligibleEnrollments = $teaching
        ->eligibleEnrollments()
        ->get();

    expect($eligibleEnrollments)
        ->toHaveCount(1);

    expect($eligibleEnrollments->first()->id)
        ->toBe($activeStudent->id);
});

test('students from another division are excluded', function () {
    $context = createTeachingEnrollmentContext();

    $otherDivision = Division::factory()->create([
        'course_id' => $context['course']->id,
        'name' => 'B',
    ]);

    createTeachingTestEnrollment(
        $context['academicYear'],
        $context['division'],
    );

    createTeachingTestEnrollment(
        $context['academicYear'],
        $otherDivision,
    );

    $teaching = Teaching::factory()->create([
        'division_id' => $context['division']->id,
        'modality_id' => null,
    ]);

    expect(
        $teaching
            ->eligibleEnrollments()
            ->count(),
    )->toBe(1);
});

function createTeachingTestTeacher(): TeacherProfile
{
    return TeacherProfile::factory()->create([
        'person_id' => Person::factory()->create()->id,
    ]);
}

function createTeachingTestDivision(): Division
{
    $academicYear = AcademicYear::factory()->create();

    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $course = Course::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
    ]);

    return Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'A',
    ]);
}

function createTeachingEnrollmentContext(): array
{
    $academicYear = AcademicYear::factory()->create();

    $level = Level::factory()->create([
        'name' => 'Nivel Secundario',
    ]);

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
        'name' => '3° Año',
    ]);

    $course = Course::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'name' => '3° Año',
    ]);

    $division = Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'A',
    ]);

    return [
        'academicYear' => $academicYear,
        'course' => $course,
        'division' => $division,
    ];
}

function createTeachingTestEnrollment(
    AcademicYear $academicYear,
    Division $division,
    ?Modality $modality = null,
    string $status = 'active',
): StudentEnrollment {
    $person = Person::factory()->create();

    $student = StudentProfile::factory()->create([
        'person_id' => $person->id,
    ]);

    return StudentEnrollment::factory()->create([
        'student_profile_id' => $student->id,
        'academic_year_id' => $academicYear->id,
        'division_id' => $division->id,
        'modality_id' => $modality?->id,
        'status' => $status,
    ]);
}