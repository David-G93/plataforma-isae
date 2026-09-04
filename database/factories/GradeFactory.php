<?php

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        $number = fake()->numberBetween(1, 7);

        return [
            'level_id' => Level::factory(),
            'name' => "{$number}° Año",
            'code' => fake()->unique()->bothify('grade-##-???'),
            'order' => $number,
            'is_active' => true,
        ];
    }
}