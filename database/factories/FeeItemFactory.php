<?php

namespace Database\Factories;

use App\Models\FeeItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeItem>
 */
class FeeItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'program_name' => fake()->randomElement(['Creche', 'Nursery', 'Playgroup', 'Primary']),
            'amount' => fake()->randomFloat(2, 50, 500),
            'frequency' => fake()->randomElement(['Termly', 'Annually', 'One-time']),
            'note' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
