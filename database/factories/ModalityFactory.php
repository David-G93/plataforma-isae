<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ModalityFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'code' => fake()->unique()->slug(2),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}