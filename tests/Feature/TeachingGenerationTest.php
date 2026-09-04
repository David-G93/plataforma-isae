<?php

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\StudyPlanSubject;
use App\Models\Subject;
use App\Models\Teaching;
use App\Services\GenerateTeachingsFromStudyPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

uses(RefreshDatabase::class);

test('teachings are generated from course study plan', function () {
    $context = createTeachingGenerationContext();

    $mathematics = Subject::factory()->create([
        'name' => 'Matemática',
        'code' => 'matematica',
    ]);

    $language = Subject::factory()->create([
        'name' => 'Lengua',
        'code' => 'lengua',
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => $mathematics->id,
        'modality_id' => null,
        'order' => 1,
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => $language->id,
        'modality_id' => null,
        'order' => 2,
    ]);

    $teachings = app(
        GenerateTeachingsFromStudyPlan::class,
    )->handle(
        $context['division'],
    );

    expect($teachings)
        ->toHaveCount(2);

    expect(
        Teaching::query()
            ->where(
                'division_id',
                $context['division']->id,
            )
            ->count(),
    )->toBe(2);
});

test('generated teaching preserves modality from study plan', function () {
    $context = createTeachingGenerationContext();

    $naturales = Modality::factory()->create([
        'name' => 'Ciencias Naturales',
        'code' => 'ciencias-naturales',
    ]);

    $subject = Subject::factory()->create([
        'name' => 'Proyecto Ciencias Naturales',
        'code' => 'proyecto-naturales',
    ]);

    $planSubject = StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => $subject->id,
        'modality_id' => $naturales->id,
    ]);

    app(
        GenerateTeachingsFromStudyPlan::class,
    )->handle(
        $context['division'],
    );

    $teaching = Teaching::query()
        ->where(
            'study_plan_subject_id',
            $planSubject->id,
        )
        ->firstOrFail();

    expect($teaching->modality_id)
        ->toBe($naturales->id);

    expect($teaching->subject_id)
        ->toBe($subject->id);
});

test('common subject generates teaching without modality', function () {
    $context = createTeachingGenerationContext();

    $subject = Subject::factory()->create([
        'name' => 'Matemática',
        'code' => 'matematica',
    ]);

    $planSubject = StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => $subject->id,
        'modality_id' => null,
    ]);

    app(
        GenerateTeachingsFromStudyPlan::class,
    )->handle(
        $context['division'],
    );

    $teaching = Teaching::query()
        ->where(
            'study_plan_subject_id',
            $planSubject->id,
        )
        ->firstOrFail();

    expect($teaching->modality_id)
        ->toBeNull();
});

test('subjects from another grade are not generated', function () {
    $context = createTeachingGenerationContext();

    $otherGrade = Grade::factory()->create([
        'level_id' => $context['level']->id,
        'name' => '4° Año',
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => Subject::factory(),
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $otherGrade->id,
        'subject_id' => Subject::factory(),
    ]);

    app(
        GenerateTeachingsFromStudyPlan::class,
    )->handle(
        $context['division'],
    );

    expect(
        Teaching::query()
            ->where(
                'division_id',
                $context['division']->id,
            )
            ->count(),
    )->toBe(1);
});

test('inactive plan subjects are not generated', function () {
    $context = createTeachingGenerationContext();

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => Subject::factory(),
        'is_active' => true,
    ]);

    StudyPlanSubject::factory()->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
        'subject_id' => Subject::factory(),
        'is_active' => false,
    ]);

    app(
        GenerateTeachingsFromStudyPlan::class,
    )->handle(
        $context['division'],
    );

    expect(
        Teaching::query()
            ->where(
                'division_id',
                $context['division']->id,
            )
            ->count(),
    )->toBe(1);
});

test('running generator twice does not duplicate teachings', function () {
    $context = createTeachingGenerationContext();

    StudyPlanSubject::factory()->count(3)->create([
        'study_plan_id' => $context['studyPlan']->id,
        'grade_id' => $context['grade']->id,
    ]);

    $generator = app(
        GenerateTeachingsFromStudyPlan::class,
    );

    $generator->handle(
        $context['division'],
    );

    $generator->handle(
        $context['division'],
    );

    expect(
        Teaching::query()
            ->where(
                'division_id',
                $context['division']->id,
            )
            ->count(),
    )->toBe(3);
});

test('generation fails when course has no study plan', function () {
    $context = createTeachingGenerationContext(
        attachStudyPlan: false,
    );

    expect(
        fn () => app(
            GenerateTeachingsFromStudyPlan::class,
        )->handle(
            $context['division'],
        ),
    )->toThrow(
        RuntimeException::class,
        'El curso no tiene un plan de estudio asignado.',
    );
});

test('generation fails when study plan belongs to another level', function () {
    $context = createTeachingGenerationContext();

    $otherLevel = Level::factory()->create();

    $wrongStudyPlan = StudyPlan::factory()->create([
        'level_id' => $otherLevel->id,
    ]);

    $context['course']->update([
        'study_plan_id' => $wrongStudyPlan->id,
    ]);

    expect(
        fn () => app(
            GenerateTeachingsFromStudyPlan::class,
        )->handle(
            $context['division']->fresh(),
        ),
    )->toThrow(
        RuntimeException::class,
        'El plan de estudio no corresponde al nivel del curso.',
    );
});

function createTeachingGenerationContext(
    bool $attachStudyPlan = true,
): array {
    $academicYear = AcademicYear::factory()->create();

    $level = Level::factory()->create([
        'name' => 'Nivel Secundario',
    ]);

    $grade = Grade::factory()->create([
        'level_id' => $level->id,
        'name' => '3° Año',
    ]);

    $studyPlan = StudyPlan::factory()->create([
        'level_id' => $level->id,
        'name' => 'Plan Secundario',
        'is_active' => true,
    ]);

    $course = Course::factory()->create([
        'academic_year_id' => $academicYear->id,
        'grade_id' => $grade->id,
        'study_plan_id' => $attachStudyPlan
            ? $studyPlan->id
            : null,
        'name' => '3° Año',
    ]);

    $division = Division::factory()->create([
        'course_id' => $course->id,
        'name' => 'A',
    ]);

    return [
        'academicYear' => $academicYear,
        'level' => $level,
        'grade' => $grade,
        'studyPlan' => $studyPlan,
        'course' => $course,
        'division' => $division,
    ];
}