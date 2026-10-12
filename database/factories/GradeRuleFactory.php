<?php

namespace Database\Factories;

use App\Models\GradeRule;
use App\Models\GradingSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeRule>
 */
class GradeRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'grade' => 'A',
            'point' => 4,
            'start_at' => 80,
            'end_at' => 100,
            'grading_system_id' => GradingSystem::factory(),
            'session_id' => fn (array $attributes) => GradingSystem::find($attributes['grading_system_id'])->session_id,
        ];
    }
}
