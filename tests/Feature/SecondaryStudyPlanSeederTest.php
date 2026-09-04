<?php

use App\Models\Course;
use App\Models\Grade;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use Database\Seeders\AcademicStructureSeeder;
use Database\Seeders\AcademicYear2026Seeder;
use Database\Seeders\SecondaryStudyPlanSeeder;

beforeEach(function () {
    $this->seed(AcademicStructureSeeder::class);
    $this->seed(AcademicYear2026Seeder::class);
    $this->seed(SecondaryStudyPlanSeeder::class);
});

it('creates bioinformatics and legacy secondary study plans', function () {
    expect(
        StudyPlan::query()
            ->where('code', 'secundario-bioinformatica')
            ->exists(),
    )->toBeTrue();

    expect(
        StudyPlan::query()
            ->where('code', 'secundario-legado')
            ->exists(),
    )->toBeTrue();
});

it('assigns bioinformatics plan only to first year in 2026', function () {
    $firstYear = Grade::query()
        ->where('code', 'secundario-1')
        ->firstOrFail();

    $secondYear = Grade::query()
        ->where('code', 'secundario-2')
        ->firstOrFail();

    $bioinformaticsPlan = StudyPlan::query()
        ->where('code', 'secundario-bioinformatica')
        ->firstOrFail();

    $legacyPlan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $firstYearCourse = Course::query()
        ->where('grade_id', $firstYear->id)
        ->firstOrFail();

    $secondYearCourse = Course::query()
        ->where('grade_id', $secondYear->id)
        ->firstOrFail();

    expect($firstYearCourse->study_plan_id)
        ->toBe($bioinformaticsPlan->id);

    expect($secondYearCourse->study_plan_id)
        ->toBe($legacyPlan->id);
});

it('first year bioinformatics subjects are common to the whole course', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-1')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-bioinformatica')
        ->firstOrFail();

    $subjects = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->get();

    expect($subjects)->toHaveCount(15);

    expect(
        $subjects
            ->whereNotNull('modality_id')
            ->count(),
    )->toBe(0);
});

it('second year has fourteen plan subjects', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-2')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $count = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->count();

    expect($count)->toBe(14);
});

it('second year informatics is academically common', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-2')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $subject = Subject::query()
        ->where('code', 'informatica')
        ->firstOrFail();

    $planSubject = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->where('subject_id', $subject->id)
        ->firstOrFail();

    expect($planSubject->modality_id)->toBeNull();
});

it('second year electronics laboratory belongs only to informatics modality', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-2')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $subject = Subject::query()
        ->where('code', 'ei-laboratorio-electronica')
        ->firstOrFail();

    $informatics = Modality::query()
        ->where('code', 'informatica')
        ->firstOrFail();

    $planSubject = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->where('subject_id', $subject->id)
        ->firstOrFail();

    expect($planSubject->modality_id)
        ->toBe($informatics->id);
});

it('all other second year subjects are common', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-2')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $electronics = Subject::query()
        ->where('code', 'ei-laboratorio-electronica')
        ->firstOrFail();

    $modalitySpecificCount = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->whereNotNull('modality_id')
        ->count();

    expect($modalitySpecificCount)->toBe(1);

    expect(
        StudyPlanSubject::query()
            ->where('study_plan_id', $plan->id)
            ->where('grade_id', $grade->id)
            ->where('subject_id', $electronics->id)
            ->whereNotNull('modality_id')
            ->exists(),
    )->toBeTrue();
});