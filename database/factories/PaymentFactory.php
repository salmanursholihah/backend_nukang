<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $method  = fake()->randomElement(['transfer', 'ewallet', 'qris', 'cash']);
        $channel = match ($method) {
            'transfer' => fake()->randomElement(['BCA', 'BNI', 'BRI', 'Mandiri']),
            'ewallet'  => fake()->randomElement(['GoPay', 'OVO', 'DANA', 'ShopeePay']),
            'qris'     => 'QRIS',
            'cash'     => null,
            default    => null,
        };
        $status  = fake()->randomElement(['unpaid', 'paid', 'pending']);

        return [
            'order_id'        => Order::factory(),
            'customer_id'     => User::factory()->customer(),
            'amount'          => fake()->numberBetween(100000, 2000000),
            'method'          => $method,
            'payment_channel' => $channel,
            'reference_id'    => $status === 'paid' ? fake()->uuid() : null,
            'snap_token'      => null,
            'payment_response' => null,
            'status'          => $status,
            'paid_at'         => $status === 'paid' ? fake()->dateTimeBetween('-7 days', 'now') : null,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status'   => 'paid',
            'paid_at'  => now(),
            'reference_id' => fake()->uuid(),
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(['status' => 'unpaid', 'paid_at' => null]);
    }
}
