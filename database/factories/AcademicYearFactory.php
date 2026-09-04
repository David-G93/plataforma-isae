<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    private static int $nextYear = 2030;

    public function definition(): array
    {
        $year = self::$nextYear++;

        return [
            'year' => $year,
            'name' => "Ciclo Lectivo {$year}",
            'starts_at' => "{$year}-03-01",
            'ends_at' => "{$year}-12-20",
            'is_active' => false,
        ];
    }
}