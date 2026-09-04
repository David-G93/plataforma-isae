<?php

use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\InstitutionalRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(
        InstitutionalRoleSeeder::class,
    );
});

test('secretary can create subject', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $this->actingAs($user)
        ->post(
            route('academic.subjects.store'),
            [
                'name' => 'Matemática',
                'code' => 'matematica',
                'description' => null,
                'is_active' => true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'subjects',
        [
            'name' => 'Matemática',
            'code' => 'matematica',
        ],
    );
});

test('secretary can create study plan', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $level = Level::factory()->create();

    $this->actingAs($user)
        ->post(
            route('academic.study-plans.store'),
            [
                'level_id' => $level->id,
                'name' => 'Plan Secundario',
                'code' => 'plan-secundario',
                'effective_from_year' => 2026,
                'effective_to_year' => null,
                'is_active' => true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'study_plans',
        [
            'level_id' => $level->id,
            'code' => 'plan-secundario',
        ],
    );
});

test('secretary can add common subject to study plan', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    $subject = Subject::factory()->create();

    $this->actingAs($user)
        ->post(
            route(
                'academic.study-plan-subjects.store',
            ),
            [
                'study_plan_id' => $studyPlan->id,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
                'modality_id' => null,
                'order' => 1,
                'is_active' => true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'study_plan_subjects',
        [
            'study_plan_id' => $studyPlan->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'modality_id' => null,
        ],
    );
});

test('secretary can add modality subject to study plan', function () {
    $user = User::factory()->create();

    $user->assignRole('secretario');

    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    $subject = Subject::factory()->create();

    $modality = Modality::factory()->create();

    $this->actingAs($user)
        ->post(
            route(
                'academic.study-plan-subjects.store',
            ),
            [
                'study_plan_id' => $studyPlan->id,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
                'modality_id' => $modality->id,
                'order' => 1,
                'is_active' => true,
            ],
        )
        ->assertRedirect(
            route('academic.index'),
        );

    $this->assertDatabaseHas(
        'study_plan_subjects',
        [
            'study_plan_id' => $studyPlan->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'modality_id' => $modality->id,
        ],
    );
});

test('study plan rejects grade from another level', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    $planLevel = Level::factory()->create();

    $otherLevel = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $otherLevel->id,
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $planLevel->id,
    ]);

    $subject = Subject::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(
            route(
                'academic.study-plan-subjects.store',
            ),
            [
                'study_plan_id' =>
                    $studyPlan->id,

                'grade_id' =>
                    $grade->id,

                'subject_id' =>
                    $subject->id,

                'modality_id' =>
                    null,

                'order' =>
                    1,

                'is_active' =>
                    true,
            ],
        );

    $response->assertSessionHasErrors(
        'grade_id',
    );
});