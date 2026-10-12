<?php

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

it('fourth year has fifteen plan subjects', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-4')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    expect(
        StudyPlanSubject::query()
            ->where('study_plan_id', $plan->id)
            ->where('grade_id', $grade->id)
            ->count(),
    )->toBe(15);
});

it('fourth year natural sciences subjects are correctly assigned', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-4')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $naturalSciences = Modality::query()
        ->where('code', 'ciencias-naturales')
        ->firstOrFail();

    $codes = [
        'biologia',
        'ecologia',
        'fisica',
    ];

    foreach ($codes as $code) {
        $subject = Subject::query()
            ->where('code', $code)
            ->firstOrFail();

        $planSubject = StudyPlanSubject::query()
            ->where('study_plan_id', $plan->id)
            ->where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        expect($planSubject->modality_id)
            ->toBe($naturalSciences->id);
    }
});

it('fourth year informatics subjects are correctly assigned', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-4')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $informatics = Modality::query()
        ->where('code', 'informatica')
        ->firstOrFail();

    $codes = [
        'comunicacion-audiovisual',
        'lenguajes-tecnicas-programacion',
        'sistemas-digitales-informacion',
    ];

    foreach ($codes as $code) {
        $subject = Subject::query()
            ->where('code', $code)
            ->firstOrFail();

        $planSubject = StudyPlanSubject::query()
            ->where('study_plan_id', $plan->id)
            ->where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        expect($planSubject->modality_id)
            ->toBe($informatics->id);
    }
});

it('all other fourth year subjects are common', function () {
    $grade = Grade::query()
        ->where('code', 'secundario-4')
        ->firstOrFail();

    $plan = StudyPlan::query()
        ->where('code', 'secundario-legado')
        ->firstOrFail();

    $specificSubjects = StudyPlanSubject::query()
        ->where('study_plan_id', $plan->id)
        ->where('grade_id', $grade->id)
        ->whereNotNull('modality_id')
        ->count();

    expect($specificSubjects)->toBe(6);
});