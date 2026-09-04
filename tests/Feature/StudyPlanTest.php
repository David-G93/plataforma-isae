<?php

use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('study plan belongs to a level', function () {
    $level = Level::factory()->create();

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    expect($studyPlan->level->id)
        ->toBe($level->id);
});

test('study plan can have several subjects', function () {
    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    StudyPlanSubject::factory()->count(3)->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
    ]);

    expect($studyPlan->fresh()->subjects)
        ->toHaveCount(3);
});

test('study plan subject belongs to grade and subject', function () {
    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $subject = Subject::factory()->create();

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlanSubject = StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'subject_id' => $subject->id,
    ]);

    expect($studyPlanSubject->grade->id)
        ->toBe($grade->id);

    expect($studyPlanSubject->subject->id)
        ->toBe($subject->id);
});

test('study plan subject can be common without modality', function () {
    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlanSubject = StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'modality_id' => null,
    ]);

    expect($studyPlanSubject->modality)
        ->toBeNull();
});

test('study plan subject can belong to modality', function () {
    $level = Level::factory()->create();

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    $modality = Modality::factory()->create([
        'name' => 'Informática',
        'code' => 'informatica',
    ]);

    $studyPlanSubject = StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'modality_id' => $modality->id,
    ]);

    expect($studyPlanSubject->modality->id)
        ->toBe($modality->id);
});

test('same grade can have common and modality subjects', function () {
    $level = Level::factory()->create([
        'name' => 'Nivel Secundario',
    ]);

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
        'name' => '3° Año',
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
    ]);

    $naturales = Modality::factory()->create([
        'name' => 'Ciencias Naturales',
        'code' => 'naturales',
    ]);

    $informatica = Modality::factory()->create([
        'name' => 'Informática',
        'code' => 'informatica',
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'subject_id' => Subject::factory()->create([
            'name' => 'Matemática',
            'code' => 'matematica',
        ])->id,
        'modality_id' => null,
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'subject_id' => Subject::factory()->create([
            'name' => 'Materia Naturales',
            'code' => 'materia-naturales',
        ])->id,
        'modality_id' => $naturales->id,
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $studyPlan->id,
        'grade_id' => $grade->id,
        'subject_id' => Subject::factory()->create([
            'name' => 'Materia Informática',
            'code' => 'materia-informatica',
        ])->id,
        'modality_id' => $informatica->id,
    ]);

    expect(
        StudyPlanSubject::query()
            ->where('study_plan_id', $studyPlan->id)
            ->where('grade_id', $grade->id)
            ->count(),
    )->toBe(3);

    expect(
        StudyPlanSubject::query()
            ->where('study_plan_id', $studyPlan->id)
            ->where('grade_id', $grade->id)
            ->whereNull('modality_id')
            ->count(),
    )->toBe(1);

    expect(
        StudyPlanSubject::query()
            ->where('study_plan_id', $studyPlan->id)
            ->where('grade_id', $grade->id)
            ->whereNotNull('modality_id')
            ->count(),
    )->toBe(2);
});