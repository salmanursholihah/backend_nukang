<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use App\Models\Order;              // ← tambah ini
use App\Models\PartnerEarning;
use App\Models\UserNotification;

class MidtransWebhookController extends Controller
{

    ////handle webhook versi lama
    // public function handle(Request $request)
    // {
    //     $payload = $request->all();
    //     Log::info('=== MIDTRANS WEBHOOK MASUK ===', $payload);

    //     // ── Verifikasi signature ───────────────────────────────
    //     $orderId     = $payload['order_id'] ?? '';
    //     $statusCode  = $payload['status_code'] ?? '';
    //     $grossAmount = $payload['gross_amount'] ?? '';
    //     $serverKey   = config('midtrans.server_key');
    //     $expectedSig = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    //     if ($expectedSig !== ($payload['signature_key'] ?? '')) {
    //         Log::warning('Midtrans: signature tidak valid', ['order_id' => $orderId]);
    //         return response()->json(['message' => 'Invalid signature'], 403);
    //     }

    //     // ── Tentukan status payment ────────────────────────────
    //     $transactionStatus = $payload['transaction_status'] ?? '';
    //     $fraudStatus       = $payload['fraud_status'] ?? 'accept';

    //     $paymentStatus = match (true) {
    //         $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
    //         $transactionStatus === 'settlement'                           => 'paid',
    //         in_array($transactionStatus, ['deny', 'cancel', 'expire'])   => 'failed',
    //         default                                                        => 'pending',
    //     };

    //     Log::info('Midtrans: resolved status', [
    //         'order_id'          => $orderId,
    //         'transaction_status' => $transactionStatus,
    //         'payment_status'    => $paymentStatus,
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         // ── Cari payment berdasarkan midtrans_order_id ─────
    //         $payment = Payment::where('midtrans_order_id', $orderId)->first();

    //         // Fallback: cari di reference_id
    //         if (!$payment) {
    //             $payment = Payment::where('reference_id', $orderId)->first();
    //         }

    //         if (!$payment) {
    //             Log::warning('Midtrans: payment tidak ditemukan', ['order_id' => $orderId]);
    //             // Return 200 agar Midtrans tidak retry terus
    //             return response()->json(['message' => 'Payment not found, ignored'], 200);
    //         }

    //         Log::info('Midtrans: payment ditemukan', [
    //             'payment_id' => $payment->id,
    //             'type'       => $payment->type,
    //             'survey_id'  => $payment->survey_id,
    //         ]);

    //         // ── Update status payment ──────────────────────────
    //         $payment->update([
    //             'status'            => $paymentStatus,
    //             'reference_id'      => $orderId,
    //             'paid_at'           => $paymentStatus === 'paid' ? now() : null,
    //             'midtrans_response' => $payload,
    //         ]);

    //         // ── Jika survey payment & paid → update survey ─────
    //         if ($payment->type === 'survey' && $paymentStatus === 'paid') {
    //             $survey = SurveyRequest::find($payment->survey_id);
    //             if ($survey) {
    //                 $survey->update(['status' => 'scheduled']);

    //                 \App\Models\UserNotification::create([
    //                     'user_id' => $survey->tukangid,
    //                     'type' => 'survey_scheduled',
    //                     'title' => 'pembayaran survey telah di konfirmasi',
    //                     'body' => 'customer telah membayat biaya survey,jadwal survey sudah di konfirmasi',
    //                     'data' => json_encode(['survey_id' => $survey->id]),
    //                     'is_read' => false,
    //                 ]);
    //                 Log::info('Survey updated to scheduled', ['survey_id' => $survey->id]);
    //             } else {
    //                 Log::warning('Survey tidak ditemukan', ['survey_id' => $payment->survey_id]);
    //             }
    //         }

    //         // ── Jika order payment & paid → update order ───────
    //         if ($payment->type !== 'survey' && $payment->order_id && $paymentStatus === 'paid') {
    //             $payment->order?->update(['status' => 'accepted']);
    //             Log::info('Order updated to accepted', ['order_id' => $payment->order_id]);
    //         }

    //         DB::commit();

    //         Log::info('Midtrans: webhook berhasil diproses', [
    //             'payment_id' => $payment->id,
    //             'status'     => $paymentStatus,
    //         ]);

    //         return response()->json(['message' => 'OK'], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Midtrans webhook error: ' . $e->getMessage(), [
    //             'order_id' => $orderId,
    //             'trace'    => $e->getTraceAsString(),
    //         ]);
    //         return response()->json(['message' => 'Server error'], 500);
    //     }
    // }

    public function handle(Request $request)
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            $notif             = new Notification();
            $orderId           = $notif->order_id;
            $transactionStatus = $notif->transaction_status;
            $fraudStatus       = $notif->fraud_status ?? 'accept';

            Log::info('[Webhook] Midtrans notification', [
                'order_id'           => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status'       => $fraudStatus,
            ]);

            // Tentukan status payment lokal
            $isPaid = in_array($transactionStatus, ['settlement', 'capture'])
                && $fraudStatus !== 'deny';

            $isFailed = in_array($transactionStatus, ['cancel', 'deny', 'expire']);

            // Cari payment berdasarkan midtrans_order_id
            $payment = Payment::where('midtrans_order_id', $orderId)->first();

            if (!$payment) {
                Log::warning('[Webhook] Payment not found for order_id: ' . $orderId);
                return response()->json(['status' => 'ok']); // Midtrans tetap butuh 200
            }

            DB::beginTransaction();
            try {
                if ($isPaid && $payment->status !== 'paid') {
                    // 1. Update payment → paid
                    $payment->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                    ]);

                    // 2. Proses berdasarkan tipe payment
                    if ($payment->type === 'order' && $payment->order_id) {
                        $this->handleOrderPaymentPaid($payment);
                    } elseif ($payment->type === 'survey' && $payment->survey_id) {
                        $this->handleSurveyPaymentPaid($payment);
                    }
                } elseif ($isFailed && $payment->status !== 'failed') {
                    $payment->update(['status' => 'failed']);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('[Webhook] Error processing payment: ' . $e->getMessage());
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('[Webhook] Midtrans notification error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // Saat order dibayar → buat PartnerEarning (status: pending)
    //
    // Earning status "pending" artinya: uang sudah masuk ke platform,
    // tapi tukang belum bisa tarik karena order belum selesai dikerjakan.
    // Status akan berubah ke "settled" saat order completed.
    // =========================================================
    private function handleOrderPaymentPaid(Payment $payment): void
    {
        $order = Order::find($payment->order_id);
        if (!$order) return;

        // Pastikan earning belum ada untuk order ini
        $existingEarning = PartnerEarning::where('order_id', $order->id)->first();
        if ($existingEarning) {
            Log::info('[Webhook] Earning sudah ada untuk order_id: ' . $order->id);
            return;
        }

        // Hitung platform fee (10% dari total)
        $platformFeeRate = 0.10;
        $orderAmount     = (float) $order->total_price;
        $platformFee     = round($orderAmount * $platformFeeRate, 2);
        $tukangAmount    = round($orderAmount - $platformFee, 2);

        PartnerEarning::create([
            'tukang_id'    => $order->tukang_id,
            'order_id'     => $order->id,
            'order_amount' => $orderAmount,
            'platform_fee' => $platformFee,
            'amount'       => $tukangAmount,
            'status'       => 'pending', // ← menunggu order selesai
        ]);

        // Update status order jika masih pending (sudah dibayar → bisa diproses)
        if ($order->status === 'pending') {
            $order->update(['status' => 'paid']); // opsional: tambah status 'paid' di enum Order
        }

        // Notifikasi ke tukang: ada order yang sudah dibayar
        UserNotification::send(
            userId: $order->tukang_id,
            title: 'Pembayaran Order Diterima',
            body: 'Customer telah membayar order. Silakan konfirmasi untuk mulai pengerjaan.',
            type: 'order_paid',
            notifiable: $order,
            data: [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ],
        );

        Log::info('[Webhook] Earning created for order_id: ' . $order->id, [
            'tukang_id'    => $order->tukang_id,
            'order_amount' => $orderAmount,
            'platform_fee' => $platformFee,
            'tukang_amount' => $tukangAmount,
        ]);
    }

    // =========================================================
    // Saat survey fee dibayar → update status survey ke scheduled
    // (Survey fee TIDAK masuk ke earning tukang — ini biaya platform)
    // =========================================================
    private function handleSurveyPaymentPaid(Payment $payment): void
    {
        $survey = SurveyRequest::find($payment->survey_id);
        if (!$survey) return;

        if ($survey->status !== 'schedule') {
            $survey->update(['status' => 'schedule']);
        }

        // Notifikasi ke tukang
        UserNotification::send(
            userId: $survey->tukang_id,
            title: 'Pembayaran Survey Dikonfirmasi',
            body: 'Customer telah membayar biaya survey. Jadwal survey sudah dikonfirmasi.',
            type: 'survey_scheduled',
            notifiable: $survey,
            data: [
                'survey_id' => $survey->id,
            ],
        );

        Log::info('[Webhook] Survey payment confirmed for survey_id: ' . $survey->id);
    }
}
