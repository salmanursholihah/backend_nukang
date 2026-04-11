<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id'    => Chat::factory(),
            'sender_id'  => User::factory(),
            'message'    => fake()->randomElement([
                'Halo, apakah bisa datang hari ini?',
                'Baik, saya akan segera datang.',
                'Berapa estimasi biayanya?',
                'Terima kasih atas pelayanannya!',
                'Bisa minta foto progress-nya?',
            ]),
            'attachment' => null,
            'type'       => 'text',
            'is_read'    => fake()->boolean(70),
        ];
    }

    public function unread(): static
    {
        return $this->state(['is_read' => false]);
    }
}
