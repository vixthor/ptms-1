<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'category' => fake()->randomElement(Payment::CATEGORIES),
            'session_id' => SchoolSession::factory(),
            'recorded_by' => User::factory()->state(['role' => 'admin']),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_method' => fake()->randomElement(['Cash', 'Bank Transfer', 'Card']),
            'gateway' => 'manual',
            'status' => Payment::STATUS_SUCCESS,
            'paid_for' => fake()->sentence(3),
            'payment_date' => fake()->date(),
        ];
    }
}
