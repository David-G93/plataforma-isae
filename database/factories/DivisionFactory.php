<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => fake()->randomElement([
                'A',
                'B',
                'C',
                'D',
            ]),
            'shift' => fake()->randomElement([
                'Mañana',
                'Tarde',
            ]),
            'is_active' => true,
        ];
    }
}