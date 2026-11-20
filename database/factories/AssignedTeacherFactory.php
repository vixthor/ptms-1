<?php

namespace Database\Factories;

use App\Models\AssignedTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignedTeacher>
 */
class AssignedTeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->state(['role' => 'teacher']),
            'class_id' => SchoolClass::factory(),
            'session_id' => fn (array $attributes) => SchoolClass::find($attributes['class_id'])->session_id,
        ];
    }
}
