<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerEarning>
 */
class PartnerEarningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderAmount = fake()->numberBetween(200000, 2000000);
        $platformFee = $orderAmount * 0.10;
        $amount      = $orderAmount - $platformFee;
        $status      = fake()->randomElement(['pending', 'settled', 'paid']);

        return [
            'tukang_id'    => User::factory()->tukang(),
            'order_id'     => Order::factory()->completed(),
            'order_amount' => $orderAmount,
            'platform_fee' => $platformFee,
            'amount'       => $amount,
            'status'       => $status,
            'settled_at'   => in_array($status, ['settled', 'paid']) ? fake()->dateTimeBetween('-30 days', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending',  'settled_at' => null]);
    }
    public function settled(): static
    {
        return $this->state(['status' => 'settled',  'settled_at' => now()]);
    }
    public function paid(): static
    {
        return $this->state(['status' => 'paid',     'settled_at' => now()->subDays(3)]);
    }
}
