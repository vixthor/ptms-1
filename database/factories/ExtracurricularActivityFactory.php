<?php

namespace Database\Factories;

use App\Models\ExtracurricularActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExtracurricularActivity>
 */
class ExtracurricularActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Sports', 'Scouts', 'Art', 'Theatre', 'Music', 'Quiz Club']),
            'description' => fake()->sentence(14),
            'sort_order' => fake()->numberBetween(0, 10),
            'published' => true,
        ];
    }

    /**
     * An activity hidden from the public site.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }
}
