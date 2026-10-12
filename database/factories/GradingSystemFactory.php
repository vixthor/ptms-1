<?php

namespace Database\Factories;

use App\Models\GradingSystem;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradingSystem>
 */
class GradingSystemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'system_name' => 'Standard Grading',
            'class_id' => SchoolClass::factory(),
            'semester_id' => Semester::factory(),
            'session_id' => fn (array $attributes) => SchoolClass::find($attributes['class_id'])->session_id,
        ];
    }
}
