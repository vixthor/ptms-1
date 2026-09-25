<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\FinalMark;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinalMark>
 */
class FinalMarkFactory extends Factory
{
    public function definition(): array
    {
        $marks = fake()->numberBetween(0, 100);

        return [
            'calculated_marks' => $marks,
            'final_marks' => $marks,
            'note' => fake()->sentence(),
            'student_id' => User::factory()->state(['role' => 'student']),
            'class_id' => fn (array $attributes) => Course::find($attributes['course_id'])->class_id,
            'section_id' => 1,
            'course_id' => Course::factory(),
            'semester_id' => fn (array $attributes) => Course::find($attributes['course_id'])->semester_id,
            'session_id' => fn (array $attributes) => Course::find($attributes['course_id'])->session_id,
        ];
    }
}
