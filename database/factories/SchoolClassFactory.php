<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\SchoolSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_name' => 'Class '.fake()->numberBetween(1, 12),
            'session_id' => SchoolSession::factory(),
        ];
    }
}
