<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TukangProfileSeeder extends Seeder
{
    public function run(): void
    {
        $tukangs  = User::where('role', 'tukang')->get();
        $services = Service::all();

        foreach ($tukangs as $tukang) {
            // Assign 3-5 service random ke tiap tukang
            $assignedServices = $services->random(min(5, $services->count()));

            foreach ($assignedServices as $service) {
                DB::table('tukang_services')->insertOrIgnore([
                    'tukang_id'    => $tukang->id,
                    'service_id'   => $service->id,
                    'custom_price' => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
