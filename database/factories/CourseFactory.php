<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'study_plan_id' => null,
            'name' => fake()->unique()->words(3, true),
            'is_active' => true,
        ];
    }
}