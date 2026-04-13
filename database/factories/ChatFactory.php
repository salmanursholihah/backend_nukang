<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'     => User::factory()->customer(),
            'tukang_id'       => User::factory()->tukang(),
            'order_id'        => null,
            'last_message_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
