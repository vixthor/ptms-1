<?php

namespace Database\Factories;

use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignedTeacher>
 */
class AssignedTeacherFactory extends Factory
{
    public function definition(): array
    {
        $course = Course::factory()->create();

        return [
            'teacher_id' => User::factory()->state(['role' => 'teacher']),
            'course_id' => $course->id,
            'class_id' => $course->class_id,
            'section_id' => 0,
            'semester_id' => $course->semester_id,
            'session_id' => $course->session_id,
        ];
    }
}
