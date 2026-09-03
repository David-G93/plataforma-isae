<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuardianProfile>
 */
class GuardianProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
        ];
    }
}