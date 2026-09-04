<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LevelFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'code' => fake()->unique()->slug(2),
            'order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}