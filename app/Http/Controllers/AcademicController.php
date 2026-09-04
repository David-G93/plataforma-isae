<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\StoreDivisionRequest;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Modality;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\Teaching;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\StoreStudyPlanRequest;
use App\Http\Requests\StoreStudyPlanSubjectRequest;
use App\Http\Requests\StoreSubjectRequest;
use App\Models\StudyPlanSubject;

class AcademicController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(
            $request->user()?->can('academic.view'),
            403,
        );

        $academicYears = AcademicYear::query()
            ->withCount([
                'courses',
                'studentEnrollments',
            ])
            ->orderByDesc('year')
            ->get()
            ->map(fn (AcademicYear $academicYear) => [
                'id' => $academicYear->id,
                'year' => $academicYear->year,
                'name' => $academicYear->name,

                'starts_at' => $academicYear
                    ->starts_at
                    ?->format('Y-m-d'),

                'ends_at' => $academicYear
                    ->ends_at
                    ?->format('Y-m-d'),

                'is_active' => $academicYear->is_active,

                'courses_count' =>
                    $academicYear->courses_count,

                'enrollments_count' =>
                    $academicYear->student_enrollments_count,
            ]);

        $levels = Level::query()
            ->with([
                'grades' => fn ($query) => $query
                    ->orderBy('order')
                    ->orderBy('name'),
            ])
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(fn (Level $level) => [
                'id' => $level->id,
                'name' => $level->name,
                'code' => $level->code,
                'is_active' => $level->is_active,

                'grades' => $level->grades
                    ->map(fn ($grade) => [
                        'id' => $grade->id,
                        'name' => $grade->name,
                        'code' => $grade->code,
                        'order' => $grade->order,
                        'is_active' => $grade->is_active,
                    ])
                    ->values(),
            ]);

        $modalities = Modality::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Modality $modality) => [
                'id' => $modality->id,
                'name' => $modality->name,
                'code' => $modality->code,
                'description' => $modality->description,
                'is_active' => $modality->is_active,
            ]);

        $studyPlans = StudyPlan::query()
    ->with([
        'level',
        'subjects.grade',
        'subjects.subject',
        'subjects.modality',
    ])
    ->withCount('subjects')
    ->orderBy('name')
    ->get()
    ->map(fn (StudyPlan $studyPlan) => [
        'id' => $studyPlan->id,
        'name' => $studyPlan->name,
        'code' => $studyPlan->code,
        'level_id' => $studyPlan->level_id,
        'level' => $studyPlan->level?->name,
        'effective_from_year' =>
            $studyPlan->effective_from_year,
        'effective_to_year' =>
            $studyPlan->effective_to_year,
        'is_active' =>
            $studyPlan->is_active,
        'subjects_count' =>
            $studyPlan->subjects_count,

        'subjects' => $studyPlan->subjects
            ->sortBy([
                ['grade.order', 'asc'],
                ['order', 'asc'],
            ])
            ->map(fn ($planSubject) => [
                'id' => $planSubject->id,
                'order' => $planSubject->order,
                'is_active' => $planSubject->is_active,

                'grade' => [
                    'id' => $planSubject->grade->id,
                    'name' => $planSubject->grade->name,
                ],

                'subject' => [
                    'id' => $planSubject->subject->id,
                    'name' => $planSubject->subject->name,
                ],

                'modality' => $planSubject->modality ? [
                    'id' => $planSubject->modality->id,
                    'name' => $planSubject->modality->name,
                ] : null,
            ])
            ->values(),
    ]);

        $courses = Course::query()
            ->with([
                'academicYear',
                'grade.level',
                'studyPlan',
                'divisions',
            ])
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'name' => $course->name,
                'is_active' => $course->is_active,

                'academic_year' => $course->academicYear ? [
                    'id' => $course->academicYear->id,
                    'year' => $course->academicYear->year,
                    'name' => $course->academicYear->name,
                ] : null,

                'grade' => $course->grade ? [
                    'id' => $course->grade->id,
                    'name' => $course->grade->name,
                    'level_id' => $course->grade->level_id,
                    'level' => $course->grade->level?->name,
                ] : null,

                'study_plan' => $course->studyPlan ? [
                    'id' => $course->studyPlan->id,
                    'name' => $course->studyPlan->name,
                ] : null,

                'divisions' => $course->divisions
                    ->sortBy('name')
                    ->map(fn (Division $division) => [
                        'id' => $division->id,
                        'name' => $division->name,
                        'shift' => $division->shift,
                        'is_active' => $division->is_active,
                    ])
                    ->values(),
            ]);

            $subjects = Subject::query()
    ->orderBy('name')
    ->get()
    ->map(fn (Subject $subject) => [
        'id' => $subject->id,
        'name' => $subject->name,
        'code' => $subject->code,
        'description' => $subject->description,
        'is_active' => $subject->is_active,
    ]);

        return Inertia::render('Academic/Index', [
            'subjects' => $subjects,

            'canManage' =>
                $request->user()?->can('academic.manage')
                ?? false,

            'summary' => [
                'academic_years' =>
                    AcademicYear::query()->count(),

                'courses' =>
                    Course::query()->count(),

                'divisions' =>
                    Division::query()->count(),

                'subjects' =>
                    Subject::query()->count(),

                'study_plans' =>
                    StudyPlan::query()->count(),

                'teachings' =>
                    Teaching::query()->count(),
            ],

            'academicYears' => $academicYears,
            'levels' => $levels,
            'modalities' => $modalities,
            'studyPlans' => $studyPlans,
            'courses' => $courses,
        ]);
    }

    public function storeAcademicYear(
        StoreAcademicYearRequest $request,
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            if ($data['is_active']) {
                AcademicYear::query()->update([
                    'is_active' => false,
                ]);
            }

            AcademicYear::query()->create($data);
        });

        return redirect()
            ->route('academic.index')
            ->with(
                'success',
                'Ciclo lectivo creado correctamente.',
            );
    }

    public function storeCourse(
        StoreCourseRequest $request,
    ): RedirectResponse {
        $data = $request->validated();

        $grade = Grade::query()
            ->with('level')
            ->findOrFail($data['grade_id']);

        if (! empty($data['study_plan_id'])) {
            $studyPlan = StudyPlan::query()
                ->findOrFail($data['study_plan_id']);

            if ($studyPlan->level_id !== $grade->level_id) {
                throw ValidationException::withMessages([
                    'study_plan_id' =>
                        'El plan de estudio no corresponde al nivel seleccionado.',
                ]);
            }
        }

        $exists = Course::query()
            ->where(
                'academic_year_id',
                $data['academic_year_id'],
            )
            ->where(
                'grade_id',
                $data['grade_id'],
            )
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'grade_id' =>
                    'Ya existe un curso para ese grado o año dentro del ciclo lectivo.',
            ]);
        }

        Course::query()->create($data);

        return redirect()
            ->route('academic.index')
            ->with(
                'success',
                'Curso creado correctamente.',
            );
    }

    public function storeDivision(
        StoreDivisionRequest $request,
    ): RedirectResponse {
        Division::query()->create(
            $request->validated(),
        );

        return redirect()
            ->route('academic.index')
            ->with(
                'success',
                'División creada correctamente.',
            );
    }

    public function storeSubject(
    StoreSubjectRequest $request,
): RedirectResponse {
    Subject::query()->create(
        $request->validated(),
    );

    return redirect()
        ->route('academic.index')
        ->with(
            'success',
            'Materia creada correctamente.',
        );
}

public function storeStudyPlan(
    StoreStudyPlanRequest $request,
): RedirectResponse {
    StudyPlan::query()->create(
        $request->validated(),
    );

    return redirect()
        ->route('academic.index')
        ->with(
            'success',
            'Plan de estudio creado correctamente.',
        );
}

public function storeStudyPlanSubject(
    StoreStudyPlanSubjectRequest $request,
): RedirectResponse {
    $data = $request->validated();

    $studyPlan = StudyPlan::query()
        ->findOrFail(
            $data['study_plan_id'],
        );

    $grade = Grade::query()
        ->findOrFail(
            $data['grade_id'],
        );

    if ($studyPlan->level_id !== $grade->level_id) {
        throw ValidationException::withMessages([
            'grade_id' =>
                'El grado o año no corresponde al nivel del plan.',
        ]);
    }

    $exists = StudyPlanSubject::query()
        ->where(
            'study_plan_id',
            $data['study_plan_id'],
        )
        ->where(
            'grade_id',
            $data['grade_id'],
        )
        ->where(
            'subject_id',
            $data['subject_id'],
        )
        ->where(function ($query) use ($data) {
            if (
                empty($data['modality_id'])
            ) {
                $query->whereNull(
                    'modality_id',
                );

                return;
            }

            $query->where(
                'modality_id',
                $data['modality_id'],
            );
        })
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'subject_id' =>
                'Esa materia ya está incluida en el plan para ese grado/año y modalidad.',
        ]);
    }

    StudyPlanSubject::query()->create(
        $data,
    );

    return redirect()
        ->route('academic.index')
        ->with(
            'success',
            'Materia agregada al plan correctamente.',
        );
}
}