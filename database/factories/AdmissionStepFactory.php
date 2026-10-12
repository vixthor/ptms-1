<?php

namespace Database\Factories;

use App\Models\AdmissionStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionStep>
 */
class AdmissionStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(12),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
