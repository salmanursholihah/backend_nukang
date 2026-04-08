<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $banks   = ['BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 'Danamon'];
        $status  = fake()->randomElement(['pending', 'processing', 'success', 'failed']);

        return [
            'tukang_id'           => User::factory()->tukang(),
            'amount'              => fake()->numberBetween(100000, 2000000),
            'bank_name'           => fake()->randomElement($banks),
            'bank_account_number' => fake()->numerify('##############'),
            'bank_account_name'   => fake()->name(),
            'reference_id'        => in_array($status, ['success']) ? fake()->uuid() : null,
            'status'              => $status,
            'notes'               => $status === 'failed' ? 'Nomor rekening tidak valid' : null,
            'processed_at'        => in_array($status, ['success', 'failed']) ? fake()->dateTimeBetween('-7 days', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending',    'processed_at' => null]);
    }
    public function success(): static
    {
        return $this->state(['status' => 'success',    'processed_at' => now(), 'reference_id' => fake()->uuid()]);
    }
    public function failed(): static
    {
        return $this->state(['status' => 'failed',     'processed_at' => now(), 'notes' => 'Nomor rekening tidak ditemukan']);
    }
}
