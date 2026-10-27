<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Modality;
use App\Models\Teaching;
use Illuminate\Support\Collection;

class AcademicStructureValidationService
{
    public function divisions(int $year): array
    {
        $allowedLegacyModalityIds = Modality::query()
            ->whereIn('code', ['ciencias-naturales', 'informatica'])
            ->pluck('id');

        $divisions = Division::query()
            ->with([
                'course.grade.level',
                'studentEnrollments' => fn ($query) => $query
                    ->where('status', 'active')
                    ->with('studentProfile.guardians'),
                'teachers',
                'staff',
            ])
            ->where('is_active', true)
            ->whereHas('course.academicYear', fn ($query) => $query->where('year', $year))
            ->get();

        $items = $divisions->map(function (Division $division) use ($allowedLegacyModalityIds) {
            $grade = $division->course->grade;
            $requiresModality = $grade->level->code === 'secundario'
                && in_array($grade->code, [
                    'secundario-2',
                    'secundario-3',
                    'secundario-4',
                    'secundario-5',
                ], true);

            $enrollments = $division->studentEnrollments;
            $missingGuardians = $enrollments->filter(
                fn ($enrollment) => $enrollment->studentProfile?->guardians?->isEmpty() ?? true,
            )->count();

            $missingModality = $requiresModality
                ? $enrollments->whereNull('modality_id')->count()
                : 0;

            $invalidModality = $requiresModality
                ? $enrollments->filter(
                    fn ($enrollment) => $enrollment->modality_id !== null
                        && ! $allowedLegacyModalityIds->contains($enrollment->modality_id),
                )->count()
                : $enrollments->whereNotNull('modality_id')->count();

            $teacherCount = $division->teachers->count();
            $staffRoles = $division->staff->pluck('pivot.assignment_role');
            $hasPreceptor = $staffRoles->contains('preceptor');
            $hasLeadership = $staffRoles->contains(
                fn ($role) => in_array($role, [
                    'gestion',
                    'rector',
                    'director',
                    'vicedirector',
                    'secretario',
                ], true),
            );

            $issues = collect();

            if ($enrollments->isEmpty()) {
                $issues->push('Sin estudiantes activos.');
            }
            if ($missingGuardians > 0) {
                $issues->push("{$missingGuardians} estudiante(s) sin responsable.");
            }
            if ($missingModality > 0) {
                $issues->push("{$missingModality} estudiante(s) sin modalidad.");
            }
            if ($invalidModality > 0) {
                $issues->push("{$invalidModality} estudiante(s) con modalidad inválida.");
            }
            if ($teacherCount === 0) {
                $issues->push('Sin docentes vinculados a la división.');
            }
            if (! $hasPreceptor) {
                $issues->push('Sin preceptor asignado.');
            }
            if (! $hasLeadership) {
                $issues->push('Sin directivo/personal de gestión asignado.');
            }

            return [
                'division_id' => $division->id,
                'students' => $enrollments->count(),
                'teachers' => $teacherCount,
                'staff' => $division->staff->count(),
                'missing_guardians' => $missingGuardians,
                'missing_modality' => $missingModality,
                'invalid_modality' => $invalidModality,
                'has_preceptor' => $hasPreceptor,
                'has_leadership' => $hasLeadership,
                'is_valid' => $issues->isEmpty(),
                'issues' => $issues->values(),
            ];
        })->values();

        return [
            'summary' => [
                'total' => $items->count(),
                'valid' => $items->where('is_valid', true)->count(),
                'with_issues' => $items->where('is_valid', false)->count(),
                'students' => $items->sum('students'),
                'missing_guardians' => $items->sum('missing_guardians'),
                'missing_modality' => $items->sum('missing_modality'),
            ],
            'items' => $items->keyBy('division_id'),
        ];
    }

    public function teachings(int $year): array
    {
        $teachings = Teaching::query()
            ->with([
                'teachers',
                'division.teachers',
                'division.studentEnrollments' => fn ($query) => $query->where('status', 'active'),
            ])
            ->where('is_active', true)
            ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', $year))
            ->get();

        $items = $teachings->map(function (Teaching $teaching) {
            $divisionTeacherIds = $teaching->division->teachers->pluck('id');
            $assignedTeacherIds = $teaching->teachers->pluck('id');
            $invalidTeacherCount = $assignedTeacherIds->diff($divisionTeacherIds)->count();

            $expectedStudents = $teaching->division->studentEnrollments
                ->when(
                    $teaching->modality_id !== null,
                    fn (Collection $enrollments) => $enrollments->where('modality_id', $teaching->modality_id),
                )
                ->count();

            $eligibleStudents = $teaching->eligibleEnrollments()->count();
            $studentMismatch = $eligibleStudents !== $expectedStudents;
            $teacherCount = $teaching->teachers->count();

            $issues = collect();

            if ($teacherCount === 0) {
                $issues->push('Sin docente asignado.');
            }
            if ($invalidTeacherCount > 0) {
                $issues->push("{$invalidTeacherCount} docente(s) no pertenecen a la división.");
            }
            if ($studentMismatch) {
                $issues->push("Alumnos elegibles {$eligibleStudents}; esperados {$expectedStudents}.");
            }
            if ($expectedStudents === 0) {
                $issues->push('Sin alumnos esperados para esta cursada.');
            }

            return [
                'teaching_id' => $teaching->id,
                'teacher_count' => $teacherCount,
                'co_teaching' => $teacherCount > 1,
                'invalid_teacher_count' => $invalidTeacherCount,
                'expected_students' => $expectedStudents,
                'eligible_students' => $eligibleStudents,
                'student_mismatch' => $studentMismatch,
                'is_valid' => $issues->isEmpty(),
                'issues' => $issues->values(),
            ];
        })->values();

        return [
            'summary' => [
                'total' => $items->count(),
                'valid' => $items->where('is_valid', true)->count(),
                'with_issues' => $items->where('is_valid', false)->count(),
                'without_teachers' => $items->where('teacher_count', 0)->count(),
                'invalid_teacher_assignments' => $items->sum('invalid_teacher_count'),
                'student_mismatches' => $items->where('student_mismatch', true)->count(),
                'co_teaching' => $items->where('co_teaching', true)->count(),
            ],
            'items' => $items->keyBy('teaching_id'),
        ];
    }

    public function qualityControls(int $year): array
    {
        $divisionsWithoutStudents = Division::query()
            ->with(['course.grade.level'])
            ->where('is_active', true)
            ->whereHas('course.academicYear', fn ($query) => $query->where('year', $year))
            ->whereDoesntHave('studentEnrollments', fn ($query) => $query->where('status', 'active'))
            ->get()
            ->map(fn (Division $division) => [
                'division_id' => $division->id,
                'course' => $division->course->name,
                'division' => $division->name,
                'level' => $division->course->grade->level->name,
            ])
            ->values();

        $studentsWithoutModality = \App\Models\StudentEnrollment::query()
            ->with(['studentProfile.person', 'division.course.grade'])
            ->where('status', 'active')
            ->whereNull('modality_id')
            ->whereHas('academicYear', fn ($query) => $query->where('year', $year))
            ->whereHas('division.course.grade', fn ($query) => $query->whereIn('code', [
                'secundario-2',
                'secundario-3',
                'secundario-4',
                'secundario-5',
            ]))
            ->get()
            ->map(fn ($enrollment) => [
                'enrollment_id' => $enrollment->id,
                'student' => $enrollment->studentProfile?->person?->full_name ?? 'Estudiante sin nombre',
                'course' => $enrollment->division?->course?->name,
                'division' => $enrollment->division?->name,
            ])
            ->values();

        $studentsWithoutGuardian = \App\Models\StudentEnrollment::query()
            ->with(['studentProfile.person', 'division.course'])
            ->where('status', 'active')
            ->whereHas('academicYear', fn ($query) => $query->where('year', $year))
            ->whereDoesntHave('studentProfile.guardians')
            ->get()
            ->map(fn ($enrollment) => [
                'enrollment_id' => $enrollment->id,
                'student' => $enrollment->studentProfile?->person?->full_name ?? 'Estudiante sin nombre',
                'course' => $enrollment->division?->course?->name,
                'division' => $enrollment->division?->name,
            ])
            ->values();

        $teachingsWithoutTeacher = Teaching::query()
            ->with(['subject', 'modality', 'division.course'])
            ->where('is_active', true)
            ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', $year))
            ->whereDoesntHave('teachers')
            ->get()
            ->map(fn (Teaching $teaching) => [
                'teaching_id' => $teaching->id,
                'subject' => $teaching->subject->name,
                'course' => $teaching->division->course->name,
                'division' => $teaching->division->name,
                'modality' => $teaching->modality?->name,
            ])
            ->values();

        $invalidTeachingTeachers = Teaching::query()
            ->with(['subject', 'division.course', 'teachers.person', 'division.teachers'])
            ->where('is_active', true)
            ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', $year))
            ->get()
            ->flatMap(function (Teaching $teaching) {
                $divisionTeacherIds = $teaching->division->teachers->pluck('id');

                return $teaching->teachers
                    ->reject(fn ($teacher) => $divisionTeacherIds->contains($teacher->id))
                    ->map(fn ($teacher) => [
                        'teaching_id' => $teaching->id,
                        'teacher_profile_id' => $teacher->id,
                        'teacher' => $teacher->person?->full_name ?? 'Docente sin nombre',
                        'subject' => $teaching->subject->name,
                        'course' => $teaching->division->course->name,
                        'division' => $teaching->division->name,
                    ]);
            })
            ->values();

        $counts = [
            'teachings_without_teacher' => $teachingsWithoutTeacher->count(),
            'divisions_without_students' => $divisionsWithoutStudents->count(),
            'students_without_modality' => $studentsWithoutModality->count(),
            'students_without_guardian' => $studentsWithoutGuardian->count(),
            'teachers_outside_division' => $invalidTeachingTeachers->count(),
        ];

        return [
            'summary' => [
                ...$counts,
                'total_issues' => array_sum($counts),
                'is_clean' => array_sum($counts) === 0,
            ],
            'teachings_without_teacher' => $teachingsWithoutTeacher,
            'divisions_without_students' => $divisionsWithoutStudents,
            'students_without_modality' => $studentsWithoutModality,
            'students_without_guardian' => $studentsWithoutGuardian,
            'teachers_outside_division' => $invalidTeachingTeachers,
        ];
    }

}
