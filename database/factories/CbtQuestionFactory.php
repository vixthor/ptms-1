<?php

namespace Database\Factories;

use App\Models\CbtQuestion;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CbtQuestion>
 */
class CbtQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'question' => fake()->sentence().'?',
            'option_a' => fake()->word(),
            'option_b' => fake()->word(),
            'option_c' => fake()->word(),
            'option_d' => fake()->word(),
            'option_e' => null,
            'correct_option' => 'a',
            'marks' => 1,
            'status' => true,
            'created_by' => User::factory()->state(['role' => 'teacher']),
        ];
    }
}
