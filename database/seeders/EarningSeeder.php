<?php

namespace Database\Seeders;

use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Database\Seeder;

class EarningSeeder extends Seeder
{
    public function run(): void
    {
        $tukangs = User::where('role', 'tukang')->get();
        $banks   = ['BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga'];
        $statuses = ['pending', 'processing', 'success', 'failed', 'pending'];

        foreach ($tukangs as $i => $tukang) {
            Withdrawal::create([
                'tukang_id'           => $tukang->id,
                'amount'              => rand(200000, 1000000),
                'bank_name'           => $banks[$i % count($banks)],
                'bank_account_number' => fake()->numerify('##############'),
                'bank_account_name'   => $tukang->name,
                'status'              => $statuses[$i % count($statuses)],
                'processed_at'        => in_array($statuses[$i % count($statuses)], ['success', 'failed']) ? now() : null,
            ]);
        }
    }
}
