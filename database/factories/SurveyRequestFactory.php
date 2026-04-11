<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyRequest>
 */
class SurveyRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            'requested',
            'accepted',
            'on_survey',
            'survey_priced',
            'approved',
            'rejected',
            'cancelled'
        ]);

        $hasPriced = in_array($status, ['survey_priced', 'approved']);

        return [
            'customer_id'     => User::factory()->customer(),
            'tukang_id'       => User::factory()->tukang(),
            'service_id'      => Service::factory(),
            'address'         => fake()->streetAddress() . ', Yogyakarta',
            'latitude'        => fake()->randomFloat(7, -7.85, -7.75),
            'longitude'       => fake()->randomFloat(7, 110.33, 110.43),
            'survey_date'     => fake()->dateTimeBetween('now', '+14 days'),
            'survey_fee'      => $hasPriced ? fake()->numberBetween(50000, 200000) : null,
            'estimated_price' => $hasPriced ? fake()->numberBetween(500000, 5000000) : null,
            'estimated_days'  => $hasPriced ? fake()->numberBetween(1, 14) : null,
            'notes'           => fake()->optional()->sentence(),
            'tukang_notes'    => $hasPriced ? fake()->optional()->sentence() : null,
            'status'          => $status,
        ];
    }

    public function requested(): static
    {
        return $this->state(['status' => 'requested']);
    }
    public function approved(): static
    {
        return $this->state(['status' => 'approved',  'estimated_price' => 1500000, 'estimated_days' => 3]);
    }
}
