<?php

namespace Database\Factories;

use App\Models\ParentMessage;
use App\Models\SchoolSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentMessage>
 */
class ParentMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => SchoolSession::factory(),
            'sender_id' => User::factory()->state(['role' => 'admin']),
            'audience' => ParentMessage::AUDIENCE_SCHOOL,
            'class_id' => null,
            'body' => fake()->sentence(12),
            'channel' => ParentMessage::CHANNEL_WHATSAPP,
            'recipients' => [['phone' => '2348031234567', 'name' => 'Parent of '.fake()->firstName()]],
            'recipients_count' => 1,
            'status' => ParentMessage::STATUS_PREPARED,
        ];
    }
}
