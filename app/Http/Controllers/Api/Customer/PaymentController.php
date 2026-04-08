<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
     // =========================================================
    // STORE — Bayar order
    // POST /api/customer/payments
    // Body:
    //   order_id         : int (required)
    //   method           : transfer|ewallet|qris|cash|credit_card (required)
    //   payment_channel  : string (optional) contoh: BCA, GoPay, OVO
    // =========================================================
 
   public function store(Request $request): JsonResponse
{
    $request->validate([
        'order_id'        => 'required|exists:orders,id',
        'method'          => 'required|in:transfer,ewallet,qris,cash,credit_card',
        'payment_channel' => 'nullable|string|max:50',
    ]);

    $order = Order::with('payment')->find($request->order_id);

    // Pastikan order milik customer ini
    if ($order->customer_id !== $request->user()->id) {
        return response()->json([
            'status'  => false,
            'message' => 'Order tidak ditemukan.',
        ], 404);
    }

    // Cek status order valid untuk bayar
    if (! in_array($order->status, ['pending', 'accepted'])) {
        return response()->json([
            'status'  => false,
            'message' => 'Order tidak bisa dibayar dengan status saat ini.',
        ], 422);
    }

    // Cek sudah pernah bayar
    if ($order->payment && $order->payment->status === 'paid') {
        return response()->json([
            'status'  => false,
            'message' => 'Order ini sudah dibayar.',
        ], 422);
    }

    $paymentMethod  = $request->input('method');
    $paymentChannel = $request->input('payment_channel');

    DB::beginTransaction();
    try {
        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id'     => $request->user()->id,
                'amount'          => $order->total_price,
                'method'          => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'status'          => 'pending',
            ]
        );

        // Jika cash → langsung paid
        // Selain cash → nanti dihandle callback dari gateway
        if ($paymentMethod === 'cash') {
            $payment->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        }

        // TODO: Integrasi Midtrans Snap untuk non-cash
        // $snapToken = $this->createMidtransTransaction($order, $payment);
        // $payment->update(['snap_token' => $snapToken]);

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => $paymentMethod === 'cash'
                ? 'Pembayaran cash berhasil dicatat.'
                : 'Pembayaran diinisiasi. Selesaikan pembayaran sesuai metode yang dipilih.',
            'data'    => $this->formatPayment($payment->fresh()),
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => false,
            'message' => 'Gagal memproses pembayaran.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
 
 
    // =========================================================
    // SHOW — Cek status pembayaran
    // GET /api/customer/payments/{payment}
    // =========================================================
 
    public function show(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Pembayaran tidak ditemukan.',
            ], 404);
        }
 
        $payment->load('order:id,order_number,status,total_price');
 
        return response()->json([
            'status' => true,
            'data'   => $this->formatPayment($payment),
        ]);
    }
 
 
    // =========================================================
    // CALLBACK — Webhook dari payment gateway (Midtrans, dll)
    // POST /api/customer/payments/{payment}/callback
    // =========================================================
 
    public function callback(Request $request, Payment $payment): JsonResponse
    {
        // TODO: Verifikasi signature Midtrans
        // $signatureKey = hash('sha512',
        //     $request->order_id . $request->status_code .
        //     $request->gross_amount . config('midtrans.server_key')
        // );
        // if ($signatureKey !== $request->signature_key) {
        //     return response()->json(['status' => false, 'message' => 'Invalid signature.'], 403);
        // }
 
        $request->validate([
            'transaction_status' => 'required|string',
            'reference_id'       => 'nullable|string',
        ]);
 
        DB::beginTransaction();
        try {
            $status = match ($request->transaction_status) {
                'settlement', 'capture'      => 'paid',
                'pending'                    => 'pending',
                'deny', 'cancel', 'expire'   => 'failed',
                default                      => 'pending',
            };
 
            $updateData = [
                'status'           => $status,
                'reference_id'     => $request->reference_id,
                'payment_response' => $request->all(),
            ];
 
            if ($status === 'paid') {
                $updateData['paid_at'] = now();
            }
 
            $payment->update($updateData);
 
            DB::commit();
 
            return response()->json([
                'status'  => true,
                'message' => 'Callback berhasil diproses.',
            ]);
 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses callback.',
            ], 500);
        }
    }
 
 
    // =========================================================
    // HELPERS
    // =========================================================
 
    private function formatPayment(Payment $payment): array
    {
        return [
            'id'              => $payment->id,
            'order_id'        => $payment->order_id,
            'amount'          => $payment->amount,
            'method'          => $payment->method,
            'payment_channel' => $payment->payment_channel,
            'reference_id'    => $payment->reference_id,
            'snap_token'      => $payment->snap_token,
            'status'          => $payment->status,
            'paid_at'         => $payment->paid_at?->toDateTimeString(),
            'created_at'      => $payment->created_at->toDateTimeString(),
            'order'           => $payment->relationLoaded('order') ? [
                'id'           => $payment->order->id,
                'order_number' => $payment->order->order_number,
                'status'       => $payment->order->status,
                'total_price'  => $payment->order->total_price,
            ] : null,
        ];
    }
}
