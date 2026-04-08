<?php

namespace Database\Seeders;

use App\Models\TukangLocation;
use App\Models\TukangProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1 Admin tetap ────────────────────────────────────
        User::create([
            'name'              => 'Admin Nukang',
            'email'             => 'admin@nukang.com',
            'phone'             => '081234567890',
            'role'              => 'admin',
            'password'          => Hash::make('password'),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // ── 5 Customer dummy ─────────────────────────────────
        User::factory()->customer()->count(5)->create();

        // ── 5 Tukang dummy + profil + lokasi ─────────────────
        User::factory()->tukang()->count(5)->create()->each(function (User $tukang) {
            TukangProfile::factory()->create(['user_id' => $tukang->id]);
            TukangLocation::factory()->online()->create(['tukang_id' => $tukang->id]);
        });
    }
}
