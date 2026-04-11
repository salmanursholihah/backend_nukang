<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TukangProfile>
 */
class TukangProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Koordinat sekitar Yogyakarta
        $lat = fake()->randomFloat(7, -7.85, -7.75);
        $lng = fake()->randomFloat(7, 110.33, 110.43);

        return [
            'user_id'       => User::factory()->tukang(),
            'address'       => fake()->streetAddress(),
            'city'          => fake()->randomElement(['Yogyakarta', 'Sleman', 'Bantul', 'Kulon Progo', 'Gunung Kidul']),
            'province'      => 'Daerah Istimewa Yogyakarta',
            'latitude'      => $lat,
            'longitude'     => $lng,
            'photo'         => null,
            'bio'           => fake()->sentence(10),
            'id_card_photo' => null,
            'rating'        => fake()->randomFloat(2, 3.0, 5.0),
            'total_jobs'    => fake()->numberBetween(0, 50),
            'total_reviews' => fake()->numberBetween(0, 30),
            'is_verified'   => fake()->boolean(70), // 70% kemungkinan verified
            'is_available'  => fake()->boolean(80),
            'radius_km'     => fake()->randomElement([5, 10, 15, 20]),
        ];
    }

    public function verified(): static
    {
        return $this->state(['is_verified' => true, 'is_available' => true]);
    }

    public function unverified(): static
    {
        return $this->state(['is_verified' => false]);
    }
}
