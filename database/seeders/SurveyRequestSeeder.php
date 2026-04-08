<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\SurveyRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveyRequestSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $tukangs   = User::where('role', 'tukang')->get();
        $services  = Service::all();

        $statuses = ['requested', 'accepted', 'survey_priced', 'approved', 'cancelled'];

        foreach ($statuses as $status) {
            $hasPriced = in_array($status, ['survey_priced', 'approved']);

            SurveyRequest::create([
                'customer_id'     => $customers->random()->id,
                'tukang_id'       => $tukangs->random()->id,
                'service_id'      => $services->random()->id,
                'address'         => 'Jl. ' . fake()->streetName() . ', Yogyakarta',
                'latitude'        => fake()->randomFloat(7, -7.85, -7.75),
                'longitude'       => fake()->randomFloat(7, 110.33, 110.43),
                'survey_date'     => now()->addDays(rand(1, 10)),
                'survey_fee'      => $hasPriced ? 100000 : null,
                'estimated_price' => $hasPriced ? rand(500000, 3000000) : null,
                'estimated_days'  => $hasPriced ? rand(1, 7) : null,
                'notes'           => 'Mohon datang sesuai jadwal.',
                'status'          => $status,
            ]);
        }
    }
}
