<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentOrderSeeder extends Seeder
{
    public function run(): void
    {
        // ambil user pertama (biar tidak error foreign key)
        $user = User::first();

        if (!$user) {
            $this->command->warn('User tidak ditemukan, jalankan UserSeeder dulu!');
            return;
        }

        // ── 1. Pending VA ───────────────────────────────
        PaymentOrder::create([
            'order_id'         => 'ORDER-' . Str::upper(Str::random(8)),
            'user_id'          => $user->id,
            'gross_amount'     => 150000,
            'bank'             => 'bca',
            'va_number'        => '1234567890123456',
            'payment_status'   => 'pending',
            'expired_at'       => Carbon::now()->addDay(),
            'midtrans_response'=> json_encode([
                'status' => 'pending',
                'bank'   => 'bca'
            ]),
        ]);

        // ── 2. Settlement (sudah dibayar) ───────────────
        PaymentOrder::create([
            'order_id'         => 'ORDER-' . Str::upper(Str::random(8)),
            'user_id'          => $user->id,
            'gross_amount'     => 250000,
            'bank'             => 'bni',
            'va_number'        => '9876543210123456',
            'payment_status'   => 'settlement',
            'transaction_id'   => 'TRX-' . Str::random(10),
            'paid_at'          => Carbon::now()->subMinutes(30),
            'midtrans_response'=> json_encode([
                'status' => 'settlement',
                'bank'   => 'bni'
            ]),
        ]);

        // ── 3. Expired ─────────────────────────────────
        PaymentOrder::create([
            'order_id'         => 'ORDER-' . Str::upper(Str::random(8)),
            'user_id'          => $user->id,
            'gross_amount'     => 100000,
            'bank'             => 'bri',
            'va_number'        => '1122334455667788',
            'payment_status'   => 'expire',
            'expired_at'       => Carbon::now()->subDay(),
            'midtrans_response'=> json_encode([
                'status' => 'expire',
                'bank'   => 'bri'
            ]),
        ]);

        // ── 4. Cancel ──────────────────────────────────
        PaymentOrder::create([
            'order_id'         => 'ORDER-' . Str::upper(Str::random(8)),
            'user_id'          => $user->id,
            'gross_amount'     => 300000,
            'bank'             => 'mandiri',
            'payment_status'   => 'cancel',
            'midtrans_response'=> json_encode([
                'status' => 'cancel',
                'bank'   => 'mandiri'
            ]),
        ]);
        PaymentOrder::create([
            'order_id'       => 'ORDER-' . Str::upper(Str::random(8)),
            'user_id'        => $user->id,
            'gross_amount'   => 50000,
            'bank'           => 'bca',
            'va_number'      => '1234567890123456',
            'payment_status' => 'settlement',
            'transaction_id' => 'TRX-' . Str::random(10),
            'paid_at'        => Carbon::now(),

            'midtrans_response' => json_encode([
                    'status' => 'cancel',
                'bank'   => 'mandiri'
            ]),
        ]);
    }
}
