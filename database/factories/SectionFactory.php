<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section_name' => 'Section '.fake()->randomLetter(),
            'room_no' => (string) fake()->numberBetween(100, 300),
            'class_id' => SchoolClass::factory(),
            'session_id' => fn (array $attributes) => SchoolClass::find($attributes['class_id'])->session_id,
        ];
    }
}
