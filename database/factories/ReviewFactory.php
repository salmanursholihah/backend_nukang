<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tags = fake()->randomElements(
            ['tepat waktu', 'rapi', 'ramah', 'profesional', 'harga terjangkau', 'hasil bagus', 'komunikatif'],
            fake()->numberBetween(1, 3)
        );

        return [
            'order_id'    => Order::factory()->completed(),
            'customer_id' => User::factory()->customer(),
            'tukang_id'   => User::factory()->tukang(),
            'rating'      => fake()->numberBetween(3, 5),
            'comment'     => fake()->optional(0.8)->paragraph(2),
            'tags'        => $tags,
            'is_published' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function lowRating(): static
    {
        return $this->state(['rating' => fake()->numberBetween(1, 2)]);
    }
}
