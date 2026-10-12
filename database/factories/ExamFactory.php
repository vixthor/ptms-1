<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_name' => fake()->randomElement(Exam::CA_EXAM_NAMES),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'course_id' => Course::factory(),
            'class_id' => fn (array $attributes) => Course::find($attributes['course_id'])->class_id,
            'semester_id' => fn (array $attributes) => Course::find($attributes['course_id'])->semester_id,
            'session_id' => fn (array $attributes) => Course::find($attributes['course_id'])->session_id,
        ];
    }
}
