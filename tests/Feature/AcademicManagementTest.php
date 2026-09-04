<?php

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\StudyPlan;
use App\Models\User;
use Database\Seeders\InstitutionalRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(
        InstitutionalRoleSeeder::class,
    );
});

test('secretary can create academic year', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $this->actingAs($user)
        ->post(
            route('academic.years.store'),
            [
                'year' => 2027,
                'name' => 'Ciclo Lectivo 2027',
                'starts_at' => '2027-03-01',
                'ends_at' => '2027-12-17',
                'is_active' => true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'academic_years',
        [
            'year' => 2027,
            'is_active' => true,
        ],
    );
});

test('creating active academic year deactivates previous one', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    AcademicYear::factory()->create([
        'year' => 2026,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(
            route('academic.years.store'),
            [
                'year' => 2027,
                'name' => 'Ciclo Lectivo 2027',
                'starts_at' => '2027-03-01',
                'ends_at' => '2027-12-17',
                'is_active' => true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    expect(
        AcademicYear::query()
            ->where('year', 2026)
            ->firstOrFail()
            ->is_active,
    )->toBeFalse();

    expect(
        AcademicYear::query()
            ->where('year', 2027)
            ->firstOrFail()
            ->is_active,
    )->toBeTrue();
});

test('preceptor cannot manage academic structure', function () {
    $user = User::factory()->create();

    $user->assignRole('preceptor');

    $this->actingAs($user)
        ->post(
            route('academic.years.store'),
            [
                'year' => 2027,
                'name' => 'Ciclo Lectivo 2027',
                'starts_at' => '2027-03-01',
                'ends_at' => '2027-12-17',
                'is_active' => true,
            ],
        )
        ->assertForbidden();
});

test('secretary can create course', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $academicYear =
        AcademicYear::factory()->create();

    $level =
        Level::factory()->create();

    $grade =
        Grade::factory()->create([
            'level_id' => $level->id,
        ]);

    $studyPlan =
        StudyPlan::factory()->create([
            'level_id' => $level->id,
        ]);

    $this->actingAs($user)
        ->post(
            route('academic.courses.store'),
            [
                'academic_year_id' =>
                    $academicYear->id,

                'grade_id' =>
                    $grade->id,

                'study_plan_id' =>
                    $studyPlan->id,

                'name' =>
                    '3° Año',

                'is_active' =>
                    true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'courses',
        [
            'academic_year_id' =>
                $academicYear->id,

            'grade_id' =>
                $grade->id,

            'study_plan_id' =>
                $studyPlan->id,
        ],
    );
});

test('course rejects study plan from another level', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    $academicYear =
        AcademicYear::factory()->create();

    $level =
        Level::factory()->create();

    $otherLevel =
        Level::factory()->create();

    $grade =
        Grade::factory()->create([
            'level_id' => $level->id,
        ]);

    $studyPlan =
        StudyPlan::factory()->create([
            'level_id' => $otherLevel->id,
        ]);

    $response = $this
        ->actingAs($user)
        ->post(
            route('academic.courses.store'),
            [
                'academic_year_id' =>
                    $academicYear->id,

                'grade_id' =>
                    $grade->id,

                'study_plan_id' =>
                    $studyPlan->id,

                'name' =>
                    '3° Año',

                'is_active' =>
                    true,
            ],
        );

    $response->assertSessionHasErrors(
        'study_plan_id',
    );
});

test('admin can create division', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    $academicYear =
        AcademicYear::factory()->create();

    $level =
        Level::factory()->create();

    $grade =
        Grade::factory()->create([
            'level_id' => $level->id,
        ]);

    $course =
        \App\Models\Course::factory()->create([
            'academic_year_id' =>
                $academicYear->id,

            'grade_id' =>
                $grade->id,
        ]);

    $this->actingAs($user)
        ->post(
            route('academic.divisions.store'),
            [
                'course_id' =>
                    $course->id,

                'name' =>
                    'A',

                'shift' =>
                    'Mañana',

                'is_active' =>
                    true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'divisions',
        [
            'course_id' =>
                $course->id,

            'name' =>
                'A',

            'shift' =>
                'Mañana',
        ],
    );
});