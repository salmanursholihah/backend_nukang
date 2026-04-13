<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProgress>
 */
class OrderProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'    => Order::factory(),
            'title'       => fake()->randomElement([
                'Persiapan alat dan bahan',
                'Mulai pengerjaan',
                'Progress 50%',
                'Hampir selesai',
                'Pengerjaan selesai',
            ]),
            'description' => fake()->optional()->sentence(),
            'photo'       => null,
        ];
    }
}
