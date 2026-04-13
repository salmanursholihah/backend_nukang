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
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $tukangs   = User::where('role', 'tukang')->get();
        $services  = Service::all();

        // Buat 5 order dengan berbagai status
        $statuses = ['pending', 'accepted', 'on_progress', 'completed', 'cancelled'];

        foreach ($statuses as $status) {
            $customer = $customers->random();
            $tukang   = $tukangs->random();
            $service  = $services->random();

            $subtotal   = $service->base_price * rand(1, 3);
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
                'started_at'    => in_array($status, ['on_progress', 'completed']) ? now()->subDays(2) : null,
                'completed_at'  => $status === 'completed' ? now()->subDay() : null,
                'notes'         => 'Tolong kerjakan dengan rapi.',
                'status'        => $status,
                'cancel_reason' => $status === 'cancelled' ? 'Jadwal berubah.' : null,
            ]);

            // Order detail
            OrderDetail::create([
                'order_id'     => $order->id,
                'service_id'   => $service->id,
                'service_name' => $service->name,
                'price'        => $service->base_price,
                'qty'          => rand(1, 3),
                'subtotal'     => $subtotal,
            ]);

            // Payment untuk semua order
            Payment::create([
                'order_id'        => $order->id,
                'customer_id'     => $customer->id,
                'amount'          => $totalPrice,
                'method'          => 'transfer',
                'payment_channel' => 'BCA',
                'status'          => $status === 'completed' ? 'paid' : 'unpaid',
                'paid_at'         => $status === 'completed' ? now()->subDay() : null,
            ]);

            // Progress untuk on_progress & completed
            if (in_array($status, ['on_progress', 'completed'])) {
                OrderProgress::create([
                    'order_id'    => $order->id,
                    'title'       => 'Mulai pengerjaan',
                    'description' => 'Sudah mulai mengerjakan pekerjaan.',
                    'photo'       => null,
                ]);
            }

            // Review & Earning untuk completed
            if ($status === 'completed') {
                Review::create([
                    'order_id'    => $order->id,
                    'customer_id' => $customer->id,
                    'tukang_id'   => $tukang->id,
                    'rating'      => rand(4, 5),
                    'comment'     => 'Tukang sangat profesional dan hasil kerja rapi.',
                    'tags'        => ['rapi', 'tepat waktu', 'ramah'],
                    'is_published' => true,
                ]);

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
