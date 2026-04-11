<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal   = fake()->numberBetween(100000, 2000000);
        $serviceFee = $subtotal * 0.10;
        $totalPrice = $subtotal + $serviceFee;

        $status     = fake()->randomElement(['pending', 'accepted', 'on_progress', 'completed', 'cancelled']);

        return [
            'order_number'      => 'NKG-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id'       => User::factory()->customer(),
            'tukang_id'         => User::factory()->tukang(),
            'survey_request_id' => null,
            'address'           => fake()->streetAddress() . ', Yogyakarta',
            'latitude'          => fake()->randomFloat(7, -7.85, -7.75),
            'longitude'         => fake()->randomFloat(7, 110.33, 110.43),
            'subtotal'          => $subtotal,
            'service_fee'       => $serviceFee,
            'total_price'       => $totalPrice,
            'service_date'      => fake()->dateTimeBetween('now', '+30 days'),
            'started_at'        => in_array($status, ['on_progress', 'completed']) ? fake()->dateTimeBetween('-7 days', 'now') : null,
            'completed_at'      => $status === 'completed' ? fake()->dateTimeBetween('-3 days', 'now') : null,
            'notes'             => fake()->optional()->sentence(),
            'status'            => $status,
            'cancel_reason'     => $status === 'cancelled' ? fake()->sentence() : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending',     'started_at' => null, 'completed_at' => null]);
    }
    public function accepted(): static
    {
        return $this->state(['status' => 'accepted',    'started_at' => null, 'completed_at' => null]);
    }
    public function onProgress(): static
    {
        return $this->state(['status' => 'on_progress', 'started_at' => now(), 'completed_at' => null]);
    }
    public function completed(): static
    {
        return $this->state(['status' => 'completed',   'started_at' => now()->subDays(2), 'completed_at' => now()]);
    }
    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled',   'cancel_reason' => 'Dibatalkan oleh customer', 'started_at' => null, 'completed_at' => null]);
    }
}
