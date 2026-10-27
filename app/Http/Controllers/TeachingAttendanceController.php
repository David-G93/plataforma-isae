<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Teaching;
use App\Models\TeachingAttendance;
use App\Services\AttendanceAccessService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeachingAttendanceController extends Controller
{
    public function index(
        Request $request,
        AttendanceAccessService $access,
    ): Response {
        abort_unless($request->user()?->can('attendance.view'), 403);

        $user = $request->user();
        $academicYears = AcademicYear::query()
            ->orderByDesc('year')
            ->get(['id', 'year', 'name', 'is_active']);

        $selectedYear = (int) (
            $request->integer('year')
            ?: $academicYears->firstWhere('is_active', true)?->year
            ?: $academicYears->first()?->year
            ?: now()->year
        );

        $selectedLevelId = $request->integer('level') ?: null;
        $selectedCourseId = $request->integer('course') ?: null;
        $selectedDivisionId = $request->integer('division') ?: null;
        $selectedSubjectId = $request->integer('subject') ?: null;

        $availableTeachings = $access->teachingQueryFor($user)
            ->with([
                'subject',
                'modality',
                'division.course.academicYear',
                'division.course.grade.level',
                'teachers.person',
            ])
            ->whereHas(
                'division.course.academicYear',
                fn ($query) => $query->where('year', $selectedYear),
            )
            ->get();

        $levels = $availableTeachings
            ->map(fn (Teaching $teaching) => [
                'id' => $teaching->division->course->grade->level->id,
                'name' => $teaching->division->course->grade->level->name,
                'order' => $teaching->division->course->grade->level->order,
            ])
            ->unique('id')
            ->sortBy('order')
            ->values();

        $courses = $availableTeachings
            ->filter(fn (Teaching $teaching) => ! $selectedLevelId
                || $teaching->division->course->grade->level_id === $selectedLevelId)
            ->map(fn (Teaching $teaching) => [
                'id' => $teaching->division->course->id,
                'name' => $teaching->division->course->name,
                'grade_order' => $teaching->division->course->grade->order,
            ])
            ->unique('id')
            ->sortBy('grade_order')
            ->values();

        $divisions = $availableTeachings
            ->filter(fn (Teaching $teaching) => (! $selectedLevelId
                || $teaching->division->course->grade->level_id === $selectedLevelId)
                && (! $selectedCourseId || $teaching->division->course_id === $selectedCourseId))
            ->map(fn (Teaching $teaching) => [
                'id' => $teaching->division->id,
                'name' => $teaching->division->name,
                'course' => $teaching->division->course->name,
            ])
            ->unique('id')
            ->sortBy(fn ($division) => $division['course'].' '.$division['name'])
            ->values();

        $subjects = $availableTeachings
            ->filter(fn (Teaching $teaching) => (! $selectedLevelId
                || $teaching->division->course->grade->level_id === $selectedLevelId)
                && (! $selectedCourseId || $teaching->division->course_id === $selectedCourseId)
                && (! $selectedDivisionId || $teaching->division_id === $selectedDivisionId))
            ->map(fn (Teaching $teaching) => [
                'id' => $teaching->subject->id,
                'name' => $teaching->subject->name,
            ])
            ->unique('id')
            ->sortBy(fn ($subject) => mb_strtolower($subject['name']))
            ->values();

        $teachings = $availableTeachings
            ->filter(fn (Teaching $teaching) => (! $selectedLevelId
                || $teaching->division->course->grade->level_id === $selectedLevelId)
                && (! $selectedCourseId || $teaching->division->course_id === $selectedCourseId)
                && (! $selectedDivisionId || $teaching->division_id === $selectedDivisionId)
                && (! $selectedSubjectId || $teaching->subject_id === $selectedSubjectId))
            ->sortBy([
                ['division.course.grade.level.order', 'asc'],
                ['division.course.grade.order', 'asc'],
                ['subject.name', 'asc'],
            ])
            ->values()
            ->map(fn (Teaching $teaching) => [
                'id' => $teaching->id,
                'subject' => $teaching->subject->name,
                'subject_id' => $teaching->subject->id,
                'modality' => $teaching->modality?->name,
                'course' => $teaching->division->course->name,
                'course_id' => $teaching->division->course->id,
                'division' => $teaching->division->name,
                'division_id' => $teaching->division->id,
                'level' => $teaching->division->course->grade->level->name,
                'level_id' => $teaching->division->course->grade->level->id,
                'students_count' => $teaching->eligibleEnrollments()->count(),
                'teachers' => $teaching->teachers
                    ->map(fn ($teacher) => $teacher->person?->full_name)
                    ->filter()
                    ->values(),
            ]);

        return Inertia::render('Attendance/Teachings/Index', [
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'teachings' => $teachings,
            'canViewOfficial' => $access->officialDivisionQueryFor($user)->exists(),
            'canViewAll' => $access->canViewAll($user),
            'filters' => [
                'level' => $selectedLevelId,
                'course' => $selectedCourseId,
                'division' => $selectedDivisionId,
                'subject' => $selectedSubjectId,
            ],
            'filterOptions' => [
                'levels' => $levels,
                'courses' => $courses,
                'divisions' => $divisions,
                'subjects' => $subjects,
            ],
        ]);
    }

    public function show(
        Request $request,
        Teaching $teaching,
        AttendanceAccessService $access,
    ): Response {
        abort_unless($request->user()?->can('attendance.view'), 403);
        abort_unless(
            $access->teachingQueryFor($request->user())->whereKey($teaching->id)->exists(),
            403,
        );

        $teaching->load([
            'subject',
            'modality',
            'division.course.academicYear',
            'division.course.grade.level',
            'teachers.person',
        ]);

        $academicYear = $teaching->division->course->academicYear;
        $requestedDate = $request->string('date')->toString();
        $date = $requestedDate !== ''
            ? Carbon::parse($requestedDate)->startOfDay()
            : now()->startOfDay();

        if ($date->lt($academicYear->starts_at) || $date->gt($academicYear->ends_at)) {
            $date = $academicYear->starts_at->copy();
        }

        $eligible = $teaching->eligibleEnrollments()
            ->with(['studentProfile.person', 'modality'])
            ->get()
            ->sortBy(fn ($enrollment) => mb_strtolower($enrollment->studentProfile?->person?->full_name ?? ''))
            ->values();

        $records = TeachingAttendance::query()
            ->where('teaching_id', $teaching->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->get()
            ->keyBy('student_enrollment_id');

        return Inertia::render('Attendance/Teachings/Show', [
            'teaching' => [
                'id' => $teaching->id,
                'subject' => $teaching->subject->name,
                'modality' => $teaching->modality?->name,
                'course' => $teaching->division->course->name,
                'division' => $teaching->division->name,
                'level' => $teaching->division->course->grade->level->name,
                'academic_year' => $academicYear->year,
                'starts_at' => $academicYear->starts_at->toDateString(),
                'ends_at' => $academicYear->ends_at->toDateString(),
            ],
            'date' => $date->toDateString(),
            'backFilters' => [
                'year' => $request->integer('year') ?: $academicYear->year,
                'level' => $request->integer('level') ?: null,
                'course' => $request->integer('course') ?: null,
                'division' => $request->integer('division') ?: null,
                'subject' => $request->integer('subject') ?: null,
            ],
            'canManage' => $access->canManageTeaching($request->user(), $teaching),
            'statuses' => [
                ['value' => 'present', 'label' => 'Presente', 'short' => 'P'],
                ['value' => 'absent', 'label' => 'Ausente', 'short' => 'A'],
                ['value' => 'late', 'label' => 'Tarde', 'short' => 'T'],
                ['value' => 'justified', 'label' => 'Justificado', 'short' => 'J'],
            ],
            'students' => $eligible->map(function ($enrollment) use ($records) {
                $record = $records->get($enrollment->id);

                return [
                    'enrollment_id' => $enrollment->id,
                    'name' => $enrollment->studentProfile?->person?->full_name ?? 'Estudiante sin nombre',
                    'dni' => $enrollment->studentProfile?->person?->dni,
                    'modality' => $enrollment->modality?->name,
                    'status' => $record?->status ?? 'present',
                    'notes' => $record?->notes,
                ];
            }),
        ]);
    }

    public function update(
        Request $request,
        Teaching $teaching,
        AttendanceAccessService $access,
    ): RedirectResponse {
        abort_unless($access->canManageTeaching($request->user(), $teaching), 403);
        abort_unless($teaching->is_active, 422);

        $teaching->load('division.course.academicYear');
        $academicYear = $teaching->division->course->academicYear;

        $data = $request->validate([
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.student_enrollment_id' => ['required', 'integer', 'distinct', 'exists:student_enrollments,id'],
            'records.*.status' => ['required', Rule::in(TeachingAttendance::STATUSES)],
            'records.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $date = Carbon::parse($data['date'])->startOfDay();

        if ($date->lt($academicYear->starts_at) || $date->gt($academicYear->ends_at)) {
            throw ValidationException::withMessages([
                'date' => 'La fecha debe pertenecer al ciclo lectivo de la cursada.',
            ]);
        }

        $eligibleIds = $teaching->eligibleEnrollments()->pluck('id');
        $submittedIds = collect($data['records'])->pluck('student_enrollment_id')->map(fn ($id) => (int) $id);

        if ($submittedIds->diff($eligibleIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'records' => 'Hay estudiantes que no pertenecen a esta cursada.',
            ]);
        }

        DB::transaction(function () use ($data, $date, $teaching, $request) {
            foreach ($data['records'] as $record) {
                TeachingAttendance::query()->updateOrCreate(
                    [
                        'teaching_id' => $teaching->id,
                        'student_enrollment_id' => $record['student_enrollment_id'],
                        'attendance_date' => $date->toDateString(),
                    ],
                    [
                        'status' => $record['status'],
                        'notes' => $record['notes'] ?? null,
                        'recorded_by_user_id' => $request->user()->id,
                    ],
                );
            }
        });

        return back()->with('success', 'Asistencia de la cursada guardada.');
    }
}
