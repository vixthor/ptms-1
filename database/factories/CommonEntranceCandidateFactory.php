<?php

namespace Database\Factories;

use App\Models\CommonEntranceCandidate;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommonEntranceCandidate>
 */
class CommonEntranceCandidateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'session_id' => SchoolSession::factory(),
            'registration_number' => fake()->bothify('NCEE/####/###'),
            'score' => null,
            'admitted_school' => null,
        ];
    }

    public function placed(): static
    {
        return $this->state(fn () => [
            'score' => fake()->numberBetween(150, 300),
            'admitted_school' => 'Federal Government College '.fake()->city(),
        ]);
    }
}
