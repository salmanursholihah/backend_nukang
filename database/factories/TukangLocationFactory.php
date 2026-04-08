<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TukangLocation>
 */
class TukangLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tukang_id'   => User::factory()->tukang(),
            'latitude'    => fake()->randomFloat(7, -7.85, -7.75),
            'longitude'   => fake()->randomFloat(7, 110.33, 110.43),
            'is_online'   => fake()->boolean(60),
            'last_seen_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ];
    }

    public function online(): static
    {
        return $this->state(['is_online' => true, 'last_seen_at' => now()]);
    }

    public function offline(): static
    {
        return $this->state(['is_online' => false]);
    }
}
