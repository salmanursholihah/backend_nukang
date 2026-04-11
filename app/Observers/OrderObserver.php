<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\PartnerEarning;
use App\Models\TukangProfile;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->isDirty('status')) return;

        if ($order->status === 'completed') {
            TukangProfile::where('user_id', $order->tukang_id)->increment('total_jobs');

            $fee = $order->total_price * 0.10; // 10% komisi platform

            PartnerEarning::create([
                'tukang_id'    => $order->tukang_id,
                'order_id'     => $order->id,
                'order_amount' => $order->total_price,
                'platform_fee' => $fee,
                'amount'       => $order->total_price - $fee,
                'status'       => 'pending',
            ]);
        }
    }
}
