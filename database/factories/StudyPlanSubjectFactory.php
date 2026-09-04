<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\StudyPlan;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyPlanSubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'study_plan_id' => StudyPlan::factory(),
            'grade_id' => Grade::factory(),
            'subject_id' => Subject::factory(),
            'modality_id' => null,
            'order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}