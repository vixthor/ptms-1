<?php

namespace Database\Factories;

use App\Models\AcademicProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AcademicProgram>
 */
class AcademicProgramFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Creche', 'Nursery', 'Playgroup', 'Primary']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 10),
            'published' => true,
        ];
    }
}
