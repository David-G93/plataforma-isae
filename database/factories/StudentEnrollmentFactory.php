<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Modality;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'division_id' => Division::factory(),
            'modality_id' => null,
            'status' => 'active',
            'enrolled_at' => fake()->date(),
            'ended_at' => null,
            'notes' => null,
        ];
    }

    public function withModality(): static
    {
        return $this->state(fn () => [
            'modality_id' => Modality::factory(),
        ]);
    }
}