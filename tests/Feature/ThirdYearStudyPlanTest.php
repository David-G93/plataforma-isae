<?php

use App\Models\Grade;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\StudyPlanSubjectModalityRule;
use App\Models\Subject;
use Database\Seeders\AcademicStructureSeeder;
use Database\Seeders\AcademicYear2026Seeder;
use Database\Seeders\SecondaryStudyPlanSeeder;

beforeEach(function () {
    $this->seed(AcademicStructureSeeder::class);
    $this->seed(AcademicYear2026Seeder::class);
    $this->seed(SecondaryStudyPlanSeeder::class);
});

it('third year has fourteen active plan subjects', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    expect(
        StudyPlanSubject::query()
            ->where('study_plan_id', $plan->id)
            ->where('grade_id', $grade->id)
            ->where('is_active', true)
            ->count(),
    )->toBe(14);
});

it('third year geology belongs only to natural sciences', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $subject = Subject::query()
        ->where('code', 'geologia')
        ->firstOrFail();

    $naturalSciences = Modality::query()
        ->where('code', 'ciencias-naturales')
        ->firstOrFail();

    $planSubject = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->where('subject_id', $subject->id)
        ->firstOrFail();

    expect($planSubject->modality_id)
        ->toBe($naturalSciences->id);
});

it('third year digital systems belongs only to informatics', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $subject = Subject::query()
        ->where('code', 'sistemas-digitales-informacion')
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

it('third year tics belongs only to informatics', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $subject = Subject::query()
        ->where('code', 'tics-aplicaciones')
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

it('third year chemistry remains common to the whole course', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $chemistry = Subject::query()
        ->where('code', 'quimica')
        ->firstOrFail();

    $planSubject = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->where('subject_id', $chemistry->id)
        ->firstOrFail();

    expect($planSubject->modality_id)
        ->toBeNull();
});

it('chemistry counts as curricular and institutional for natural sciences', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $chemistry = Subject::query()
        ->where('code', 'quimica')
        ->firstOrFail();

    $naturalSciences = Modality::query()
        ->where('code', 'ciencias-naturales')
        ->firstOrFail();

    $planSubject = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->where('subject_id', $chemistry->id)
        ->firstOrFail();

    $rule = StudyPlanSubjectModalityRule::query()
        ->where('study_plan_subject_id', $planSubject->id)
        ->where('modality_id', $naturalSciences->id)
        ->firstOrFail();

    expect($rule->counts_for_curricular_average)
        ->toBeTrue();

    expect($rule->counts_for_institutional_average)
        ->toBeTrue();
});

it('chemistry counts only as institutional for informatics', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-3')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $chemistry = Subject::query()
        ->where('code', 'quimica')
        ->firstOrFail();

    $informatics = Modality::query()
        ->where('code', 'informatica')
        ->firstOrFail();

    $planSubject = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->where('subject_id', $chemistry->id)
        ->firstOrFail();

    $rule = StudyPlanSubjectModalityRule::query()
        ->where('study_plan_subject_id', $planSubject->id)
        ->where('modality_id', $informatics->id)
        ->firstOrFail();

    expect($rule->counts_for_curricular_average)
        ->toBeFalse();

    expect($rule->counts_for_institutional_average)
        ->toBeTrue();
});