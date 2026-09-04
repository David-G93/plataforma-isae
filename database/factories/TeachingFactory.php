<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Modality;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeachingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'study_plan_subject_id' => null,
            'division_id' => Division::factory(),
            'modality_id' => null,
            'name' => null,
            'is_active' => true,
        ];
    }

    public function withModality(): static
    {
        return $this->state(fn () => [
            'modality_id' => Modality::factory(),
        ]);
    }
}