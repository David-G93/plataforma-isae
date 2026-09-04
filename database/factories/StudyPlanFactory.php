<?php

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'name' => 'Plan '.fake()->unique()->bothify('####'),
            'code' => fake()->unique()->bothify('plan-####-????'),
            'effective_from_year' => fake()->numberBetween(2020, 2030),
            'effective_to_year' => null,
            'is_active' => true,
        ];
    }
}