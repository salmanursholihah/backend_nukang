<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProgress;
use App\Models\PartnerEarning;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $tukangs   = User::where('role', 'tukang')->get();
        $services  = Service::all();

        // Buat 5 order dengan berbagai status
        $statuses = [
            'pending',
            'accepted',
            'on_progress',
            'completed',
            'cancelled'
        ];

        foreach ($statuses as $status) {

            $customer = $customers->random();
            $tukang   = $tukangs->random();
            $service  = $services->random();

            $qty = rand(1, 3);

            $subtotal   = $service->base_price * $qty;
            $serviceFee = $subtotal * 0.10;
            $totalPrice = $subtotal + $serviceFee;

            $order = Order::create([
                'order_number'  => 'NKG-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),

                'customer_id'   => $customer->id,
                'tukang_id'     => $tukang->id,

                'address'       => 'Jl. ' . fake()->streetName() . ', Yogyakarta',

                'latitude'      => fake()->randomFloat(7, -7.85, -7.75),
                'longitude'     => fake()->randomFloat(7, 110.33, 110.43),

                'subtotal'      => $subtotal,
                'service_fee'   => $serviceFee,
                'total_price'   => $totalPrice,

                'service_date'  => now()->addDays(rand(1, 14)),

                'started_at'    => in_array($status, ['on_progress', 'completed'])
                    ? now()->subDays(2)
                    : null,

                'completed_at'  => $status === 'completed'
                    ? now()->subDay()
                    : null,

                'notes'         => 'Tolong kerjakan dengan rapi.',

                'status'        => $status,

                'cancel_reason' => $status === 'cancelled'
                    ? 'Jadwal berubah.'
                    : null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ORDER DETAIL
            |--------------------------------------------------------------------------
            */

            OrderDetail::create([
                'order_id'     => $order->id,
                'service_id'   => $service->id,
                'service_name' => $service->name,
                'price'        => $service->base_price,
                'qty'          => $qty,
                'subtotal'     => $subtotal,
            ]);

            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            Payment::create([

                // relasi polymorphic
                'payable_id'   => $order->id,
                'payable_type' => Order::class,

                // relasi biasa
                'order_id'     => $order->id,
                'customer_id'  => $customer->id,
                'user_id'      => $customer->id,

                // payment info
                'amount'           => $totalPrice,
                'method'           => 'transfer',
                'payment_channel'  => 'BCA',

                // midtrans style
                'transaction_id' => 'TRX-' . strtoupper(uniqid()),
                'payment_type'  => 'bank_transfer',
                'bank'          => 'bca',
                'va_number'     => fake()->numerify('###########'),

                'status' => match ($status) {
                    'completed'   => 'settlement',
                    'cancelled'   => 'cancel',
                    'pending'     => 'pending',
                    'accepted'    => 'pending',
                    'on_progress' => 'pending',
                    default       => 'pending',
                },

                'paid_at' => $status === 'completed'
                    ? now()->subDay()
                    : null,

                'expiry_time' => now()->addDay(),

                'midtrans_response' => [
                    'transaction_status' => $status === 'completed'
                        ? 'settlement'
                        : 'pending',

                    'payment_type' => 'bank_transfer',

                    'bank' => 'bca',

                    'va_numbers' => [
                        [
                            'bank'      => 'bca',
                            'va_number' => fake()->numerify('###########'),
                        ]
                    ]
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | ORDER PROGRESS
            |--------------------------------------------------------------------------
            */

            if (in_array($status, ['on_progress', 'completed'])) {

                OrderProgress::create([
                    'order_id'    => $order->id,
                    'title'       => 'Mulai pengerjaan',
                    'description' => 'Sudah mulai mengerjakan pekerjaan.',
                    'percent'     => 0,           // ✅ new column
                    'reported_at' => now(),       // ✅ new column (or null)
                ]);

                OrderProgress::create([
                    'order_id'    => $order->id,
                    'title'       => 'Mulai pengerjaan',
                    'description' => 'Sudah mulai mengerjakan pekerjaan.',
                    'percent'     => 25,
                    'reported_at' => now(),
                ]);
                // No photo seeding needed

                $progress = OrderProgress::create([
                    'order_id'    => $order->id,
                    'title'       => 'Mulai pengerjaan',
                    'description' => 'Sudah mulai mengerjakan pekerjaan.',
                    'percent'     => 25,
                    'reported_at' => now(),
                ]);

                // Insert photo directly — no relationship needed
                DB::table('order_progress_photos')->insert([
                    'order_progress_id' => $progress->id,
                    'photo_path'        => 'progress/sample.jpg',
                    'photo_url'         => 'https://example.com/storage/progress/sample.jpg',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | REVIEW
            |--------------------------------------------------------------------------
            */

            if ($status === 'completed') {

                Review::create([
                    'order_id'     => $order->id,
                    'customer_id'  => $customer->id,
                    'tukang_id'    => $tukang->id,

                    'rating'       => rand(4, 5),

                    'comment'      => 'Tukang sangat profesional dan hasil kerja rapi.',

                    'tags'         => [
                        'rapi',
                        'tepat waktu',
                        'ramah'
                    ],

                    'is_published' => true,
                ]);

                /*
                |--------------------------------------------------------------------------
                | PARTNER EARNING
                |--------------------------------------------------------------------------
                */

                PartnerEarning::create([
                    'tukang_id'    => $tukang->id,
                    'order_id'     => $order->id,

                    'order_amount' => $totalPrice,

                    'platform_fee' => $serviceFee,

                    'amount'       => $subtotal,

                    'status'       => 'settled',

                    'settled_at'   => now(),
                ]);
            }
        }
    }
}
