<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price    = fake()->numberBetween(50000, 500000);
        $qty      = fake()->numberBetween(1, 5);

        return [
            'order_id'     => Order::factory(),
            'service_id'   => Service::factory(),
            'service_name' => fake()->randomElement([
                'Pasang Stop Kontak',
                'Servis AC',
                'Cat Dinding',
                'Pasang Keramik',
                'Perbaikan Pipa',
            ]),
            'price'        => $price,
            'qty'          => $qty,
            'subtotal'     => $price * $qty,
        ];
    }
}
