<?php

namespace Database\Factories;

use App\Models\StudentParentInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentParentInfo>
 */
class StudentParentInfoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'father_name' => fake()->name('male'),
            'father_phone' => '080'.fake()->numerify('########'),
            'mother_name' => fake()->name('female'),
            'mother_phone' => '081'.fake()->numerify('########'),
            'parent_address' => fake()->streetAddress(),
        ];
    }
}
