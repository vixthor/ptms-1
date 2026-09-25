<?php

namespace Database\Factories;

use App\Models\Promotion;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'section_id' => Section::factory(),
            'class_id' => fn (array $attributes) => Section::find($attributes['section_id'])->class_id,
            'session_id' => fn (array $attributes) => Section::find($attributes['section_id'])->session_id,
            'id_card_number' => fake()->unique()->bothify('PTMS-####'),
        ];
    }
}
