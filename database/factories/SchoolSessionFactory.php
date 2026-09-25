<?php

namespace Database\Factories;

use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolSession>
 */
class SchoolSessionFactory extends Factory
{
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2030);

        return [
            'session_name' => "{$startYear}-".($startYear + 1),
        ];
    }
}
