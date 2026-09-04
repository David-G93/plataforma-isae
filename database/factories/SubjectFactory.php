<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $code = fake()->unique()->bothify('sub-####-????');

        return [
            'name' => ucfirst(
                fake()->unique()->words(2, true),
            ),
            'code' => $code,
            'description' => fake()
                ->optional()
                ->sentence(),
            'is_active' => true,
        ];
    }
}