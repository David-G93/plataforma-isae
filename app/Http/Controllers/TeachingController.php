<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncTeachingTeachersRequest;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Teaching;
use App\Services\AcademicStructureValidationService;
use App\Services\GenerateTeachingsFromStudyPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TeachingController extends Controller
{
    public function index(
        Request $request,
        AcademicStructureValidationService $validator,
    ): Response
    {
        abort_unless($request->user()?->can('academic.view'), 403);

        $academicYears = AcademicYear::query()
            ->orderByDesc('year')
            ->get(['id', 'year', 'name', 'is_active']);

        $selectedYear = (int) (
            $request->integer('year')
            ?: $academicYears->firstWhere('is_active', true)?->year
            ?: $academicYears->first()?->year
            ?: now()->year
        );

        $validation = $validator->teachings($selectedYear);

        $teachings = Teaching::query()
            ->with([
                'subject',
                'modality',
                'teachers.person',
                'division.teachers.person',
                'division.course.academicYear',
                'division.course.grade.level',
            ])
            ->whereHas(
                'division.course.academicYear',
                fn ($query) => $query->where('year', $selectedYear),
            )
            ->where('is_active', true)
            ->get()
            ->sortBy([
                ['division.course.grade.level.order', 'asc'],
                ['division.course.grade.order', 'asc'],
                ['division.name', 'asc'],
                ['subject.name', 'asc'],
            ])
            ->values()
            ->map(function (Teaching $teaching) use ($validation) {
                $course = $teaching->division->course;

                return [
                    'id' => $teaching->id,
                    'subject' => [
                        'id' => $teaching->subject->id,
                        'name' => $teaching->subject->name,
                    ],
                    'modality' => $teaching->modality
                        ? [
                            'id' => $teaching->modality->id,
                            'name' => $teaching->modality->name,
                        ]
                        : null,
                    'division' => [
                        'id' => $teaching->division->id,
                        'name' => $teaching->division->name,
                        'shift' => $teaching->division->shift,
                    ],
                    'course' => [
                        'id' => $course->id,
                        'name' => $course->name,
                        'grade' => $course->grade->name,
                        'level' => $course->grade->level->name,
                    ],
                    'teachers' => $teaching->teachers
                        ->map(fn ($teacher) => [
                            'id' => $teacher->id,
                            'name' => $teacher->person?->full_name ?? 'Docente sin nombre',
                        ])
                        ->values(),
                    'available_teachers' => $teaching->division->teachers
                        ->sortBy(fn ($teacher) => mb_strtolower($teacher->person?->full_name ?? ''))
                        ->map(fn ($teacher) => [
                            'id' => $teacher->id,
                            'name' => $teacher->person?->full_name ?? 'Docente sin nombre',
                        ])
                        ->values(),
                    'eligible_students_count' => $teaching->eligibleEnrollments()->count(),
                    'validation' => $validation['items']->get($teaching->id),
                ];
            });

        $summary = $validation['summary'];

        return Inertia::render('Academic/Teachings/Index', [
            'canManage' => $request->user()?->can('academic.manage') ?? false,
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'teachings' => $teachings,
            'summary' => $summary,
        ]);
    }

    public function generate(
        Request $request,
        GenerateTeachingsFromStudyPlan $generator,
    ): RedirectResponse {
        abort_unless($request->user()?->can('academic.manage'), 403);

        $data = $request->validate([
            'year' => ['required', 'integer', 'exists:academic_years,year'],
        ]);

        $divisions = Division::query()
            ->with(['course.academicYear', 'course.studyPlan'])
            ->where('is_active', true)
            ->whereHas('course.academicYear', fn ($query) => $query->where('year', $data['year']))
            ->whereHas('course', fn ($query) => $query->where('is_active', true))
            ->get();

        $generatedCount = 0;
        $errors = [];

        foreach ($divisions as $division) {
            try {
                $teachings = $generator->handle($division);
                $generatedCount += $teachings->count();
            } catch (Throwable $exception) {
                $errors[] = sprintf(
                    '%s: %s',
                    $division->course?->name ?? "División {$division->id}",
                    $exception->getMessage(),
                );
            }
        }

        if ($errors !== []) {
            return back()->withErrors([
                'generation' => implode(' | ', $errors),
            ]);
        }

        return back()->with(
            'success',
            "Dictados generados/actualizados: {$generatedCount}.",
        );
    }

    public function updateTeachers(
        SyncTeachingTeachersRequest $request,
        Teaching $teaching,
    ): RedirectResponse {
        $teacherIds = collect(
            $request->validated('teacher_profile_ids'),
        )->map(fn ($id) => (int) $id)->values();

        $allowedIds = $teaching->division
            ->teachers()
            ->pluck('teacher_profiles.id');

        $invalidIds = $teacherIds->diff($allowedIds);

        if ($invalidIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'teacher_profile_ids' => 'Solo podés asignar docentes vinculados previamente a esta división.',
            ]);
        }

        $teaching->teachers()->sync($teacherIds->all());

        return back()->with(
            'success',
            'Docentes del dictado actualizados correctamente.',
        );
    }
}
