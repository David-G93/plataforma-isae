<?php

use App\Models\GuardianProfile;
use App\Models\Modality;
use App\Models\StudentEnrollment;
use App\Models\TeacherProfile;
use App\Models\Teaching;
use Database\Seeders\Demo2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo 2026 creates complete division composition and teachings', function () {
    $this->seed(Demo2026Seeder::class);

    expect(
        StudentEnrollment::query()
            ->where('status', 'active')
            ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
            ->count(),
    )->toBe(51);

    expect(GuardianProfile::query()->count())
        ->toBe(51);

    expect(TeacherProfile::query()->count())
        ->toBeGreaterThanOrEqual(12);

    $informatics = Modality::query()
        ->where('code', 'informatica')
        ->firstOrFail();

    $naturalSciences = Modality::query()
        ->where('code', 'ciencias-naturales')
        ->firstOrFail();

    expect(
        StudentEnrollment::query()
            ->where('modality_id', $informatics->id)
            ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
            ->count(),
    )->toBe(12);

    expect(
        StudentEnrollment::query()
            ->where('modality_id', $naturalSciences->id)
            ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
            ->count(),
    )->toBe(12);

    $teachings = Teaching::query()
        ->where('is_active', true)
        ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', 2026));

    expect((clone $teachings)->count())
        ->toBe(181);

    expect(
        (clone $teachings)
            ->whereDoesntHave('teachers')
            ->count(),
    )->toBe(0);

    expect(
        (clone $teachings)
            ->get()
            ->filter(fn (Teaching $teaching) => $teaching->eligibleEnrollments()->count() === 0)
            ->count(),
    )->toBe(0);
});

test('demo 2026 can be seeded twice without duplicating its data', function () {
    $this->seed(Demo2026Seeder::class);
    $this->seed(Demo2026Seeder::class);

    expect(
        StudentEnrollment::query()
            ->where('status', 'active')
            ->whereHas('academicYear', fn ($query) => $query->where('year', 2026))
            ->count(),
    )->toBe(51);

    expect(GuardianProfile::query()->count())
        ->toBe(51);

    expect(
        Teaching::query()
            ->where('is_active', true)
            ->whereHas('division.course.academicYear', fn ($query) => $query->where('year', 2026))
            ->count(),
    )->toBe(181);
});
