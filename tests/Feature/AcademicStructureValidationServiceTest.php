<?php

use App\Models\Division;
use App\Models\StudentEnrollment;
use App\Models\Teaching;
use App\Services\AcademicStructureValidationService;
use Database\Seeders\Demo2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo divisions 2026 pass composition validation', function () {
    $this->seed(Demo2026Seeder::class);

    $report = app(AcademicStructureValidationService::class)
        ->divisions(2026);

    expect($report['summary']['total'])->toBe(12)
        ->and($report['summary']['valid'])->toBe(12)
        ->and($report['summary']['with_issues'])->toBe(0)
        ->and($report['summary']['students'])->toBe(51)
        ->and($report['summary']['missing_guardians'])->toBe(0)
        ->and($report['summary']['missing_modality'])->toBe(0);
});

test('demo teachings 2026 pass teaching validation and include co teaching', function () {
    $this->seed(Demo2026Seeder::class);

    $report = app(AcademicStructureValidationService::class)
        ->teachings(2026);

    expect($report['summary']['total'])->toBe(181)
        ->and($report['summary']['valid'])->toBe(181)
        ->and($report['summary']['with_issues'])->toBe(0)
        ->and($report['summary']['without_teachers'])->toBe(0)
        ->and($report['summary']['invalid_teacher_assignments'])->toBe(0)
        ->and($report['summary']['student_mismatches'])->toBe(0)
        ->and($report['summary']['co_teaching'])->toBeGreaterThan(0);
});

test('division validation detects missing modality in legacy secondary', function () {
    $this->seed(Demo2026Seeder::class);

    $enrollment = StudentEnrollment::query()
        ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('division.course.grade', fn ($query) => $query->where('code', 'secundario-2'))
        ->firstOrFail();

    $enrollment->update(['modality_id' => null]);

    $report = app(AcademicStructureValidationService::class)
        ->divisions(2026);

    $divisionReport = $report['items']->get($enrollment->division_id);

    expect($divisionReport['is_valid'])->toBeFalse()
        ->and($divisionReport['missing_modality'])->toBe(1);
});

test('teaching validation detects teacher outside division', function () {
    $this->seed(Demo2026Seeder::class);

    $teaching = Teaching::query()
        ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('teachers')
        ->firstOrFail();

    $teacherId = $teaching->teachers()->firstOrFail()->id;
    $teaching->division->teachers()->detach($teacherId);

    $report = app(AcademicStructureValidationService::class)
        ->teachings(2026);

    $teachingReport = $report['items']->get($teaching->id);

    expect($teachingReport['is_valid'])->toBeFalse()
        ->and($teachingReport['invalid_teacher_count'])->toBeGreaterThan(0);
});

test('demo 2026 passes all quality controls', function () {
    $this->seed(Demo2026Seeder::class);

    $report = app(AcademicStructureValidationService::class)
        ->qualityControls(2026);

    expect($report['summary']['total_issues'])->toBe(0)
        ->and($report['summary']['is_clean'])->toBeTrue()
        ->and($report['summary']['teachings_without_teacher'])->toBe(0)
        ->and($report['summary']['divisions_without_students'])->toBe(0)
        ->and($report['summary']['students_without_modality'])->toBe(0)
        ->and($report['summary']['students_without_guardian'])->toBe(0)
        ->and($report['summary']['teachers_outside_division'])->toBe(0);
});

test('quality controls detect the five requested academic issues', function () {
    $this->seed(Demo2026Seeder::class);

    $teachingWithoutTeacher = Teaching::query()
        ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('teachers')
        ->firstOrFail();
    $teachingWithoutTeacher->teachers()->detach();

    $emptyDivision = Division::query()
        ->whereHas('course.academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('studentEnrollments', fn ($query) => $query->where('status', 'active'))
        ->firstOrFail();
    $emptyDivision->studentEnrollments()->update(['status' => 'inactive']);

    $missingModality = StudentEnrollment::query()
        ->where('status', 'active')
        ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('division.course.grade', fn ($query) => $query->where('code', 'secundario-2'))
        ->firstOrFail();
    $missingModality->update(['modality_id' => null]);

    $missingGuardian = StudentEnrollment::query()
        ->where('status', 'active')
        ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('studentProfile.guardians')
        ->firstOrFail();
    $missingGuardian->studentProfile->guardians()->detach();

    $teachingOutsideDivision = Teaching::query()
        ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', 2026))
        ->whereHas('teachers')
        ->where('id', '!=', $teachingWithoutTeacher->id)
        ->firstOrFail();
    $teacher = $teachingOutsideDivision->teachers()->firstOrFail();
    $teachingOutsideDivision->division->teachers()->detach($teacher->id);

    $report = app(AcademicStructureValidationService::class)
        ->qualityControls(2026);

    expect($report['summary']['teachings_without_teacher'])->toBeGreaterThanOrEqual(1)
        ->and($report['summary']['divisions_without_students'])->toBeGreaterThanOrEqual(1)
        ->and($report['summary']['students_without_modality'])->toBeGreaterThanOrEqual(1)
        ->and($report['summary']['students_without_guardian'])->toBeGreaterThanOrEqual(1)
        ->and($report['summary']['teachers_outside_division'])->toBeGreaterThanOrEqual(1)
        ->and($report['summary']['is_clean'])->toBeFalse();
});
