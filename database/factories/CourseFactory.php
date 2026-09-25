<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_name' => fake()->unique()->randomElement(['Mathematics', 'English', 'Science', 'History', 'Geography', 'Art']),
            'course_type' => fake()->randomElement(['Core', 'General', 'Elective', 'Optional']),
            'class_id' => SchoolClass::factory(),
            'semester_id' => Semester::factory(),
            'session_id' => fn (array $attributes) => SchoolClass::find($attributes['class_id'])->session_id,
        ];
    }
}
