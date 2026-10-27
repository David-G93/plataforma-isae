<?php

use App\Models\Division;
use App\Models\OfficialPeriodAttendance;
use App\Models\SchoolPeriod;
use App\Models\Teaching;
use App\Models\TeachingAttendance;
use App\Models\User;
use Database\Seeders\Demo2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Demo2026Seeder::class);
});

test('teacher only sees teachings assigned to them', function () {
    $teacher = User::query()
        ->where('email', 'docente1@demo.isae.local')
        ->firstOrFail();

    $teacherProfileId = $teacher->person->teacherProfile->id;

    $assignedTeaching = Teaching::query()
        ->whereHas('teachers', fn ($query) => $query->where('teacher_profiles.id', $teacherProfileId))
        ->firstOrFail();

    $otherTeaching = Teaching::query()
        ->whereDoesntHave('teachers', fn ($query) => $query->where('teacher_profiles.id', $teacherProfileId))
        ->firstOrFail();

    $this->actingAs($teacher)
        ->get(route('attendance.teachings.show', $assignedTeaching))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('attendance.teachings.show', $otherTeaching))
        ->assertForbidden();
});

test('teacher can save attendance only for eligible students in assigned teaching', function () {
    $teacher = User::query()
        ->where('email', 'docente1@demo.isae.local')
        ->firstOrFail();

    $teacherProfileId = $teacher->person->teacherProfile->id;

    $teaching = Teaching::query()
        ->whereHas('teachers', fn ($query) => $query->where('teacher_profiles.id', $teacherProfileId))
        ->with('division.course.academicYear')
        ->firstOrFail();

    $enrollment = $teaching->eligibleEnrollments()->firstOrFail();

    $this->actingAs($teacher)
        ->put(route('attendance.teachings.update', $teaching), [
            'date' => '2026-10-27',
            'records' => [
                [
                    'student_enrollment_id' => $enrollment->id,
                    'status' => 'late',
                    'notes' => 'Llegó tarde.',
                ],
            ],
        ])
        ->assertRedirect();

    expect(
        TeachingAttendance::query()
            ->where('teaching_id', $teaching->id)
            ->where('student_enrollment_id', $enrollment->id)
            ->where('status', 'late')
            ->exists(),
    )->toBeTrue();
});

test('teaching attendance is independent from official bulletin attendance', function () {
    $teacher = User::query()
        ->where('email', 'docente1@demo.isae.local')
        ->firstOrFail();

    $teacherProfileId = $teacher->person->teacherProfile->id;

    $teaching = Teaching::query()
        ->whereHas('teachers', fn ($query) => $query->where('teacher_profiles.id', $teacherProfileId))
        ->firstOrFail();

    $enrollment = $teaching->eligibleEnrollments()->firstOrFail();

    TeachingAttendance::query()->create([
        'teaching_id' => $teaching->id,
        'student_enrollment_id' => $enrollment->id,
        'attendance_date' => '2026-10-27',
        'status' => 'absent',
        'recorded_by_user_id' => $teacher->id,
    ]);

    expect(OfficialPeriodAttendance::query()->count())->toBe(0);
});

test('secondary preceptor can save official attendance by period for assigned division', function () {
    $preceptor = User::query()
        ->where('email', 'preceptor.94000004@demo.isae.local')
        ->firstOrFail();

    $division = Division::query()
        ->whereHas('staff', fn ($query) => $query
            ->where('people.id', $preceptor->person_id)
            ->where('division_staff.assignment_role', 'preceptor'))
        ->whereHas('course.grade.level', fn ($query) => $query->where('code', 'secundario'))
        ->with('course.academicYear')
        ->firstOrFail();

    $period = SchoolPeriod::query()
        ->where('academic_year_id', $division->course->academic_year_id)
        ->orderBy('order')
        ->firstOrFail();

    $enrollment = $division->studentEnrollments()
        ->where('status', 'active')
        ->firstOrFail();

    $this->actingAs($preceptor)
        ->put(route('attendance.official.update', [
            'division' => $division,
            'period' => $period,
        ]), [
            'records' => [
                [
                    'student_enrollment_id' => $enrollment->id,
                    'absences' => 2.5,
                    'justified_absences' => 1,
                    'observations' => 'Dato oficial del período.',
                ],
            ],
        ])
        ->assertRedirect();

    $record = OfficialPeriodAttendance::query()
        ->where('student_enrollment_id', $enrollment->id)
        ->where('school_period_id', $period->id)
        ->firstOrFail();

    expect((float) $record->absences)->toBe(2.5)
        ->and((float) $record->justified_absences)->toBe(1.0);
});

test('primary tutor teacher can access official attendance for tutored division', function () {
    $teacher = User::query()
        ->where('email', 'docente1@demo.isae.local')
        ->firstOrFail();

    $tutoredDivision = Division::query()
        ->whereHas('staff', fn ($query) => $query
            ->where('people.id', $teacher->person_id)
            ->where('division_staff.assignment_role', 'tutor'))
        ->first();

    if (! $tutoredDivision) {
        $this->markTestSkipped('Docente demo 1 no quedó como tutor; otro docente demo cubre el caso.');
    }

    $this->actingAs($teacher)
        ->get(route('attendance.official.index', [
            'year' => 2026,
            'division' => $tutoredDivision->id,
        ]))
        ->assertOk();
});

test('teacher filters never expand teaching access beyond assigned subjects', function () {
    $teacher = User::query()
        ->where('email', 'docente1@demo.isae.local')
        ->firstOrFail();

    $teacherProfileId = $teacher->person->teacherProfile->id;

    $assignedTeaching = Teaching::query()
        ->whereHas('teachers', fn ($query) => $query->where('teacher_profiles.id', $teacherProfileId))
        ->with('division.course.academicYear')
        ->firstOrFail();

    $otherTeaching = Teaching::query()
        ->whereDoesntHave('teachers', fn ($query) => $query->where('teacher_profiles.id', $teacherProfileId))
        ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', 2026))
        ->firstOrFail();

    $response = $this->actingAs($teacher)
        ->get(route('attendance.teachings.index', [
            'year' => 2026,
            'subject' => $otherTeaching->subject_id,
        ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Teachings/Index')
        ->has('teachings', 0)
    );

    $this->actingAs($teacher)
        ->get(route('attendance.teachings.index', [
            'year' => 2026,
            'subject' => $assignedTeaching->subject_id,
        ]))
        ->assertOk();
});

test('preceptor official attendance filters remain inside assigned divisions', function () {
    $preceptor = User::query()
        ->where('email', 'preceptor.94000004@demo.isae.local')
        ->firstOrFail();

    $assignedDivision = Division::query()
        ->whereHas('staff', fn ($query) => $query
            ->where('people.id', $preceptor->person_id)
            ->where('division_staff.assignment_role', 'preceptor'))
        ->firstOrFail();

    $outsideDivision = Division::query()
        ->where('id', '!=', $assignedDivision->id)
        ->whereDoesntHave('staff', fn ($query) => $query
            ->where('people.id', $preceptor->person_id)
            ->where('division_staff.assignment_role', 'preceptor'))
        ->firstOrFail();

    $this->actingAs($preceptor)
        ->get(route('attendance.official.index', [
            'year' => 2026,
            'division' => $outsideDivision->id,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Attendance/Official/Index')
            ->where('selectedDivisionId', fn ($value) => $value !== $outsideDivision->id)
        );
});
