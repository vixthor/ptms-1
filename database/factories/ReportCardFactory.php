<?php

namespace Database\Factories;

use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCard>
 */
class ReportCardFactory extends Factory
{
    protected $model = ReportCard::class;

    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'class_id' => fake()->numberBetween(1, 10),
            'section_id' => fake()->numberBetween(1, 10),
            'semester_id' => Semester::factory(),
            'session_id' => fn (array $attributes) => Semester::find($attributes['semester_id'])->session_id,
            'house' => fake()->randomElement(['Red', 'Blue', 'Green', 'Yellow']),
            'weight' => fake()->numberBetween(15, 60).'kg',
            'height' => fake()->numberBetween(90, 170).'cm',
            'attendance_days' => fake()->numberBetween(80, 120),
            'days_school_opened' => fake()->numberBetween(80, 120),
            'school_resumes_on' => fake()->dateTimeBetween('now', '+2 months'),
            'behavioural_ratings' => [
                'Attendance' => fake()->numberBetween(1, 5),
                'Skills' => fake()->numberBetween(1, 5),
                'Punctuality' => fake()->numberBetween(1, 5),
                'Neatness' => fake()->numberBetween(1, 5),
                'Politeness' => fake()->numberBetween(1, 5),
                'Self Control' => fake()->numberBetween(1, 5),
                'Relationship' => fake()->numberBetween(1, 5),
            ],
            'class_teacher_comment' => fake()->sentence(),
            'headteacher_comment' => fake()->sentence(),
        ];
    }
}
