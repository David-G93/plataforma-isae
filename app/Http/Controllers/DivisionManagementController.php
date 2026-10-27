<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDivisionCompositionRequest;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Modality;
use App\Models\Person;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\AcademicStructureValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DivisionManagementController extends Controller
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

        $validation = $validator->divisions($selectedYear);

        $modalities = Modality::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $students = StudentProfile::query()
            ->with('person')
            ->get()
            ->sortBy(fn (StudentProfile $student) => mb_strtolower($student->person?->full_name ?? ''))
            ->values()
            ->map(fn (StudentProfile $student) => [
                'id' => $student->id,
                'name' => $student->person?->full_name ?? 'Estudiante sin nombre',
                'dni' => $student->person?->dni,
            ]);

        $teachers = TeacherProfile::query()
            ->with('person')
            ->get()
            ->sortBy(fn (TeacherProfile $teacher) => mb_strtolower($teacher->person?->full_name ?? ''))
            ->values()
            ->map(fn (TeacherProfile $teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->person?->full_name ?? 'Docente sin nombre',
                'dni' => $teacher->person?->dni,
            ]);

        $staffPeople = User::query()
            ->with(['person', 'roles'])
            ->whereHas('roles', fn ($query) => $query->whereIn('name', [
                'gestion',
                'rector',
                'director',
                'vicedirector',
                'secretario',
                'preceptor',
                'docente',
            ]))
            ->get()
            ->filter(fn (User $user) => $user->person !== null)
            ->sortBy(fn (User $user) => mb_strtolower($user->person?->full_name ?? ''))
            ->values()
            ->map(fn (User $user) => [
                'person_id' => $user->person->id,
                'name' => $user->person->full_name,
                'dni' => $user->person->dni,
                'roles' => $user->roles->pluck('name')->values(),
            ]);

        $divisions = Division::query()
            ->with([
                'course.academicYear',
                'course.grade.level',
                'studentEnrollments' => fn ($query) => $query
                    ->where('status', 'active')
                    ->with(['studentProfile.person', 'studentProfile.guardians.person', 'modality']),
                'teachers.person',
                'staff',
            ])
            ->whereHas('course.academicYear', fn ($query) => $query->where('year', $selectedYear))
            ->where('is_active', true)
            ->get()
            ->sortBy([
                ['course.grade.level.order', 'asc'],
                ['course.grade.order', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->map(function (Division $division) use ($validation) {
                $course = $division->course;
                $requiresModality = $course->grade->level->code === 'secundario'
                    && in_array($course->grade->code, [
                        'secundario-2',
                        'secundario-3',
                        'secundario-4',
                        'secundario-5',
                    ], true);

                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'shift' => $division->shift,
                    'course' => [
                        'id' => $course->id,
                        'name' => $course->name,
                        'grade_code' => $course->grade->code,
                        'grade' => $course->grade->name,
                        'level' => $course->grade->level->name,
                        'level_code' => $course->grade->level->code,
                    ],
                    'requires_modality' => $requiresModality,
                    'students' => $division->studentEnrollments
                        ->map(fn (StudentEnrollment $enrollment) => [
                            'student_profile_id' => $enrollment->student_profile_id,
                            'name' => $enrollment->studentProfile?->person?->full_name ?? 'Estudiante sin nombre',
                            'modality_id' => $enrollment->modality_id,
                            'modality_name' => $enrollment->modality?->name,
                            'guardians' => $enrollment->studentProfile?->guardians
                                ?->map(fn ($guardian) => [
                                    'id' => $guardian->id,
                                    'name' => $guardian->person?->full_name ?? 'Responsable sin nombre',
                                    'relationship' => $guardian->pivot?->relationship,
                                    'is_primary' => (bool) ($guardian->pivot?->is_primary ?? false),
                                ])
                                ->values() ?? collect(),
                        ])
                        ->values(),
                    'teacher_profile_ids' => $division->teachers->pluck('id')->values(),
                    'validation' => $validation['items']->get($division->id),
                    'staff' => $division->staff
                        ->map(fn (Person $person) => [
                            'person_id' => $person->id,
                            'name' => $person->full_name,
                            'assignment_role' => $person->pivot->assignment_role,
                        ])
                        ->values(),
                ];
            });

        return Inertia::render('Academic/Divisions/Index', [
            'canManage' => $request->user()?->can('academic.manage') ?? false,
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'modalities' => $modalities,
            'students' => $students,
            'teachers' => $teachers,
            'staffPeople' => $staffPeople,
            'divisions' => $divisions,
            'validationSummary' => $validation['summary'],
        ]);
    }

    public function update(
        UpdateDivisionCompositionRequest $request,
        Division $division,
    ): RedirectResponse {
        $division->load('course.academicYear');
        $data = $request->validated();
        $academicYearId = $division->course->academic_year_id;
        $division->loadMissing('course.grade.level');

        $requiresModality = $division->course->grade->level->code === 'secundario'
            && in_array($division->course->grade->code, [
                'secundario-2',
                'secundario-3',
                'secundario-4',
                'secundario-5',
            ], true);

        if ($requiresModality) {
            $missingModality = collect($data['students'])
                ->contains(fn ($student) => empty($student['modality_id']));

            if ($missingModality) {
                throw ValidationException::withMessages([
                    'students' => 'Todos los estudiantes de 2° a 5° Año deben tener modalidad asignada.',
                ]);
            }

            $legacyModalityIds = Modality::query()
                ->whereIn('code', ['ciencias-naturales', 'informatica'])
                ->pluck('id');

            $invalidModality = collect($data['students'])
                ->pluck('modality_id')
                ->diff($legacyModalityIds)
                ->isNotEmpty();

            if ($invalidModality) {
                throw ValidationException::withMessages([
                    'students' => 'Para 2° a 5° Año solo se permiten Ciencias Naturales o Informática.',
                ]);
            }
        } else {
            $data['students'] = collect($data['students'])
                ->map(fn ($student) => [
                    'student_profile_id' => $student['student_profile_id'],
                    'modality_id' => null,
                ])
                ->all();
        }

        $studentIds = collect($data['students'])
            ->pluck('student_profile_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $conflicts = StudentEnrollment::query()
            ->where('academic_year_id', $academicYearId)
            ->whereIn('student_profile_id', $studentIds)
            ->where('division_id', '!=', $division->id)
            ->where('status', 'active')
            ->with('studentProfile.person')
            ->get();

        if ($conflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'students' => 'Hay estudiantes con matrícula activa en otra división: '
                    .$conflicts->map(fn ($enrollment) => $enrollment->studentProfile?->person?->full_name)
                        ->filter()
                        ->join(', '),
            ]);
        }

        DB::transaction(function () use ($division, $academicYearId, $data, $studentIds) {
            StudentEnrollment::query()
                ->where('academic_year_id', $academicYearId)
                ->where('division_id', $division->id)
                ->where('status', 'active')
                ->when(
                    $studentIds->isNotEmpty(),
                    fn ($query) => $query->whereNotIn('student_profile_id', $studentIds),
                )
                ->update([
                    'status' => 'inactive',
                    'ended_at' => now()->toDateString(),
                ]);

            foreach ($data['students'] as $student) {
                StudentEnrollment::query()->updateOrCreate(
                    [
                        'student_profile_id' => $student['student_profile_id'],
                        'academic_year_id' => $academicYearId,
                    ],
                    [
                        'division_id' => $division->id,
                        'modality_id' => $student['modality_id'] ?? null,
                        'status' => 'active',
                        'ended_at' => null,
                    ],
                );
            }

            $division->teachers()->sync($data['teacher_profile_ids']);

            $staffSync = collect($data['staff'])
                ->mapWithKeys(fn ($item) => [
                    $item['person_id'] => [
                        'assignment_role' => $item['assignment_role'],
                    ],
                ])
                ->all();

            $division->staff()->sync($staffSync);

            // Si se quita un docente de la división, también deja de estar disponible
            // en sus dictados de esa división.
            foreach ($division->teachings()->with('teachers')->get() as $teaching) {
                $allowed = $teaching->teachers
                    ->pluck('id')
                    ->intersect($data['teacher_profile_ids'])
                    ->values()
                    ->all();

                $teaching->teachers()->sync($allowed);
            }
        });

        return back()->with('success', 'Composición de la división actualizada correctamente.');
    }
}
