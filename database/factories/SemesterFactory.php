<?php

namespace Database\Factories;

use App\Models\SchoolSession;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'semester_name' => fake()->randomElement(['Term 1', 'Term 2', 'Term 3']),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'session_id' => SchoolSession::factory(),
        ];
    }
}
