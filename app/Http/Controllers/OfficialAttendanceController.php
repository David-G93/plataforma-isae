<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\OfficialPeriodAttendance;
use App\Models\SchoolPeriod;
use App\Services\AttendanceAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OfficialAttendanceController extends Controller
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

        $academicYear = AcademicYear::query()->where('year', $selectedYear)->first();
        $periods = $academicYear
            ? SchoolPeriod::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('is_active', true)
                ->orderBy('order')
                ->get(['id', 'name', 'order', 'starts_at', 'ends_at'])
            : collect();

        $availableDivisions = $access->officialDivisionQueryFor($user)
            ->with([
                'course.academicYear',
                'course.grade.level',
                'studentEnrollments' => fn ($query) => $query
                    ->where('status', 'active')
                    ->with('studentProfile.person'),
            ])
            ->whereHas('course.academicYear', fn ($query) => $query->where('year', $selectedYear))
            ->get();

        $levels = $availableDivisions
            ->map(fn (Division $division) => [
                'id' => $division->course->grade->level->id,
                'name' => $division->course->grade->level->name,
                'order' => $division->course->grade->level->order,
            ])
            ->unique('id')
            ->sortBy('order')
            ->values();

        $courses = $availableDivisions
            ->filter(fn (Division $division) => ! $selectedLevelId
                || $division->course->grade->level_id === $selectedLevelId)
            ->map(fn (Division $division) => [
                'id' => $division->course->id,
                'name' => $division->course->name,
                'grade_order' => $division->course->grade->order,
            ])
            ->unique('id')
            ->sortBy('grade_order')
            ->values();

        $divisions = $availableDivisions
            ->filter(fn (Division $division) => (! $selectedLevelId
                || $division->course->grade->level_id === $selectedLevelId)
                && (! $selectedCourseId || $division->course_id === $selectedCourseId))
            ->sortBy([
                ['course.grade.level.order', 'asc'],
                ['course.grade.order', 'asc'],
                ['name', 'asc'],
            ])
            ->values();

        $requestedDivisionId = $request->integer('division') ?: null;
        $selectedDivisionId = $requestedDivisionId && $divisions->contains('id', $requestedDivisionId)
            ? $requestedDivisionId
            : $divisions->first()?->id;

        $selectedPeriodId = $request->integer('period') ?: $periods->first()?->id;

        $selectedDivision = $divisions->firstWhere('id', $selectedDivisionId);
        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId);
        $students = collect();

        if ($selectedDivision && $selectedPeriod) {
            $existing = OfficialPeriodAttendance::query()
                ->where('school_period_id', $selectedPeriod->id)
                ->whereIn('student_enrollment_id', $selectedDivision->studentEnrollments->pluck('id'))
                ->get()
                ->keyBy('student_enrollment_id');

            $students = $selectedDivision->studentEnrollments
                ->sortBy(fn ($enrollment) => mb_strtolower($enrollment->studentProfile?->person?->full_name ?? ''))
                ->values()
                ->map(function ($enrollment) use ($existing) {
                    $record = $existing->get($enrollment->id);

                    return [
                        'enrollment_id' => $enrollment->id,
                        'name' => $enrollment->studentProfile?->person?->full_name ?? 'Estudiante sin nombre',
                        'dni' => $enrollment->studentProfile?->person?->dni,
                        'absences' => $record?->absences ?? '0.00',
                        'justified_absences' => $record?->justified_absences ?? '0.00',
                        'observations' => $record?->observations,
                    ];
                });
        }

        return Inertia::render('Attendance/Official/Index', [
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
            'divisions' => $divisions->map(fn (Division $division) => [
                'id' => $division->id,
                'label' => $division->course->name.' · División '.$division->name,
                'level' => $division->course->grade->level->name,
            ]),
            'selectedDivisionId' => $selectedDivisionId,
            'students' => $students,
            'canManage' => $selectedDivision
                ? $access->canManageOfficialDivision($user, $selectedDivision)
                : false,
            'canViewAll' => $access->canViewAll($user),
            'filters' => [
                'level' => $selectedLevelId,
                'course' => $selectedCourseId,
            ],
            'filterOptions' => [
                'levels' => $levels,
                'courses' => $courses,
            ],
            'boletinField' => 'absences',
        ]);
    }

    public function update(
        Request $request,
        Division $division,
        SchoolPeriod $period,
        AttendanceAccessService $access,
    ): RedirectResponse {
        abort_unless($access->canManageOfficialDivision($request->user(), $division), 403);

        $division->load('course.academicYear');

        if ($period->academic_year_id !== $division->course->academic_year_id) {
            abort(422, 'El período no corresponde al ciclo de la división.');
        }

        $data = $request->validate([
            'records' => ['required', 'array'],
            'records.*.student_enrollment_id' => ['required', 'integer', 'distinct', 'exists:student_enrollments,id'],
            'records.*.absences' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'records.*.justified_absences' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'records.*.observations' => ['nullable', 'string', 'max:1000'],
        ]);

        $eligibleIds = $division->studentEnrollments()
            ->where('status', 'active')
            ->pluck('id');

        $submittedIds = collect($data['records'])
            ->pluck('student_enrollment_id')
            ->map(fn ($id) => (int) $id);

        if ($submittedIds->diff($eligibleIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'records' => 'Hay estudiantes que no pertenecen a esta división.',
            ]);
        }

        foreach ($data['records'] as $record) {
            if ((float) $record['justified_absences'] > (float) $record['absences']) {
                throw ValidationException::withMessages([
                    'records' => 'Las inasistencias justificadas no pueden superar las inasistencias totales.',
                ]);
            }
        }

        DB::transaction(function () use ($data, $period, $request) {
            foreach ($data['records'] as $record) {
                OfficialPeriodAttendance::query()->updateOrCreate(
                    [
                        'student_enrollment_id' => $record['student_enrollment_id'],
                        'school_period_id' => $period->id,
                    ],
                    [
                        'absences' => $record['absences'],
                        'justified_absences' => $record['justified_absences'],
                        'observations' => $record['observations'] ?? null,
                        'recorded_by_user_id' => $request->user()->id,
                    ],
                );
            }
        });

        return back()->with('success', 'Inasistencias oficiales del período guardadas.');
    }
}
