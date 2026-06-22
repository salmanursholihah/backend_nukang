<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;

class PaymentController extends Controller
{
    // =========================================================
    // STORE — Bayar order biasa
    // POST /api/customer/payments
    // =========================================================
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'        => 'required|exists:orders,id',
            'method'          => 'required|in:transfer,ewallet,qris,cash,credit_card',
            'payment_channel' => 'nullable|string|max:50',
        ]);

        $order = Order::with('payment')->find($request->order_id);

        if ($order->customer_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        if (!in_array($order->status, ['pending', 'accepted'])) {
            return response()->json(['status' => false, 'message' => 'Order tidak bisa dibayar dengan status saat ini.'], 422);
        }

        if ($order->payment && $order->payment->status === 'paid') {
            return response()->json(['status' => false, 'message' => 'Order ini sudah dibayar.'], 422);
        }

        $paymentMethod  = $request->input('method');
        $paymentChannel = $request->input('payment_channel');

        DB::beginTransaction();
        try {
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'customer_id'     => $request->user()->id,
                    'user_id'         => $request->user()->id,
                    'amount'          => $order->total_price,
                    'method'          => $paymentMethod,
                    'payment_channel' => $paymentChannel,
                    'status'          => 'pending',
                    'type'            => 'order',
                ]
            );

            if ($paymentMethod === 'cash') {
                $payment->update(['status' => 'paid', 'paid_at' => now()]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => $paymentMethod === 'cash' ? 'Pembayaran cash berhasil dicatat.' : 'Pembayaran diinisiasi.',
                'data'    => $this->formatPayment($payment->fresh()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Gagal memproses pembayaran.', 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // SHOW — Cek status pembayaran
    // GET /api/customer/payments/{payment}
    // =========================================================
    public function show(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->customer_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Pembayaran tidak ditemukan.'], 404);
        }

        if ($payment->type !== 'survey' && $payment->order_id) {
            $payment->load('order:id,order_number,status,total_price');
        }

        return response()->json(['status' => true, 'data' => $this->formatPayment($payment)]);
    }

    // =========================================================
    // CALLBACK — Webhook manual dari Flutter (opsional)
    // POST /api/customer/payments/{payment}/callback
    // =========================================================
    public function callback(Request $request, Payment $payment): JsonResponse
    {
        $request->validate([
            'transaction_status' => 'required|string',
            'reference_id'       => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $status = match ($request->transaction_status) {
                'settlement', 'capture'    => 'paid',
                'pending'                  => 'pending',
                'deny', 'cancel', 'expire' => 'failed',
                default                    => 'pending',
            };

            $updateData = [
                'status'       => $status,
                'reference_id' => $request->reference_id,
            ];

            if ($status === 'paid') {
                $updateData['paid_at'] = now();
            }

            $payment->update($updateData);
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Callback berhasil diproses.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Gagal memproses callback.'], 500);
        }
    }

    // =========================================================
    // STORE SURVEY FEE
    // POST /api/customer/surveys/{survey}/payment
    // =========================================================
    public function storeSurveyFee(Request $request, $surveyId): JsonResponse
    {
        $request->validate([
            'method'          => 'required|in:transfer,ewallet,qris,cash,credit_card',
            'payment_channel' => 'nullable|string|max:50',
        ]);

        $survey = SurveyRequest::find($surveyId);

        if (!$survey) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        if ($survey->customer_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        if (!in_array($survey->status, ['approved', 'waiting_payment', 'scheduled'])) {
            return response()->json(['status' => false, 'message' => 'Survey belum disetujui tukang atau sudah dibayar.'], 422);
        }

        if (!$survey->survey_fee || $survey->survey_fee <= 0) {
            return response()->json(['status' => false, 'message' => 'Biaya survey belum ditentukan.'], 422);
        }

        $paymentMethod  = $request->input('method');
        $paymentChannel = $request->input('payment_channel');

        DB::beginTransaction();
        try {
            // ✅ Generate midtrans_order_id di sini — ini yang akan dipakai
            //    di createVirtualAccount dan dicocokkan oleh webhook
            $midtransOrderId = 'SURVEY-' . $survey->id . '-' . time();

            $payment = Payment::updateOrCreate(
                ['survey_id' => $survey->id],
                [
                    'customer_id'       => $request->user()->id,
                    'user_id'           => $request->user()->id,
                    'amount'            => $survey->survey_fee,
                    'method'            => $paymentMethod,
                    'payment_channel'   => $paymentChannel,
                    'status'            => 'pending',
                    'type'              => 'survey',
                    // ✅ Simpan di sini agar webhook bisa menemukan payment ini
                    'midtrans_order_id' => $midtransOrderId,
                ]
            );

            if ($paymentMethod === 'cash') {
                $payment->update(['status' => 'paid', 'paid_at' => now()]);
                $survey->update(['status' => 'scheduled']);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => $paymentMethod === 'cash' ? 'Pembayaran cash berhasil dicatat.' : 'Pembayaran survey diinisiasi.',
                'data'    => $this->formatSurveyPayment($payment->fresh(), $survey),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeSurveyFee error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal memproses pembayaran.', 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // CREATE VIRTUAL ACCOUNT (Midtrans Core API)
    // POST /api/customer/payments/virtual-account
    //
    // ✅ FIX UTAMA:
    // Flutter harus kirim `payment_id` (id dari tabel payments)
    // agar VA ini terhubung ke payment yang sudah dibuat
    // di storeSurveyFee / store
    // =========================================================
    public function createVirtualAccount(Request $request): JsonResponse
    {
        $request->validate([
            'gross_amount' => 'required|numeric|min:10000',
            'bank'         => 'required|in:bca,bni,bri,mandiri,permata,cimb',
            'items'        => 'required|array',
            // ✅ payment_id wajib dikirim dari Flutter
            'payment_id'   => 'required|exists:payments,id',
        ]);

        // ✅ Ambil payment yang sudah dibuat di storeSurveyFee
        $payment = Payment::find($request->payment_id);

        // Guard: pastikan milik user ini
        if ($payment->customer_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // ✅ Gunakan midtrans_order_id yang sudah tersimpan di payment
        //    BUKAN buat order_id baru yang random
        $orderId = $payment->midtrans_order_id;

        // Jika belum ada (edge case), generate dan simpan sekarang
        if (!$orderId) {
            $orderId = 'ORDER-' . strtoupper(Str::random(8)) . '-' . time();
            $payment->update(['midtrans_order_id' => $orderId]);
        }

        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');

        $user = $request->user();

        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id'     => $orderId,  // ✅ sama dengan yang di DB
                'gross_amount' => (int) $request->gross_amount,
            ],
            'bank_transfer' => ['bank' => $request->bank],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'item_details' => $request->items,
        ];

        if ($request->bank === 'mandiri') {
            $params['payment_type'] = 'echannel';
            $params['echannel'] = [
                'bill_info1' => 'Pembayaran',
                'bill_info2' => 'Order ' . $orderId,
            ];
            unset($params['bank_transfer']);
        }

        try {
            $response = CoreApi::charge($params);

            $vaNumber = null;
            if (isset($response->va_numbers[0]->va_number)) {
                $vaNumber = $response->va_numbers[0]->va_number;
            } elseif (isset($response->payment_code)) {
                $vaNumber = $response->payment_code;
            }

            // ✅ Update payment yang SAMA dengan va_number & transaction_id
            //    Tidak perlu tabel PaymentOrder terpisah!
            $expiredAt = now()->addDay();
            $payment->update([
                'va_number'      => $vaNumber,
                'bank'           => $request->bank,
                'transaction_id' => $response->transaction_id ?? null,
                'expiry_time'    => $expiredAt,
            ]);

            Log::info('VA created', [
                'payment_id' => $payment->id,
                'order_id'   => $orderId,
                'va_number'  => $vaNumber,
            ]);

            return response()->json([
                'success'    => true,
                'order_id'   => $orderId,
                'payment_id' => $payment->id,
                'va_number'  => $vaNumber,
                'bank'       => $request->bank,
                'amount'     => $request->gross_amount,
                'expired_at' => $expiredAt,
            ], 201);
        } catch (\Exception $e) {
            Log::error('createVirtualAccount error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // CHECK STATUS — Polling dari Flutter
    // GET /api/payment/status/{orderId}
    // Dipanggil saat user tap "Cek Status Pembayaran"
    // =========================================================
    public function checkStatus(Request $request, $orderId): JsonResponse
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            // Cek ke Midtrans langsung
            $midtransStatus = \Midtrans\Transaction::status($orderId);
            $transactionStatus = $midtransStatus->transaction_status;

            // Update DB jika sudah settlement
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $payment = Payment::where('midtrans_order_id', $orderId)->first();
                if ($payment && $payment->status !== 'paid') {
                    $payment->update(['status' => 'paid', 'paid_at' => now()]);

                    // Update survey jika tipe survey
                    if ($payment->type === 'survey' && $payment->survey_id) {
                        SurveyRequest::find($payment->survey_id)
                            ?->update(['status' => 'scheduled']);
                    }
                }
            }

            if ($payment->type === 'survey' && $payment->survey_id) {
                $surveyReq = SurveyREquest::find($payment->survey_id);
                if ($surveyReq && $surveyReq->status !== 'scheduled'){
                    $surveyReq->update(['status' => 'scheduled']);

                      \App\Models\UserNotification::create([
                        'user_id' => $surveyReq->tukangid,
                        'type' => 'survey_scheduled',
                        'title' => 'pembayaran survey telah di konfirmasi',
                        'body' => 'customer telah membayat biaya survey,jadwal survey sudah di konfirmasi',
                        'data' => json_encode(['survey_id' => $surveyReq>id]),
                        'is_read' => false,
                    ]);
                }
            }

            return response()->json([
                'status'             => true,
                'transaction_status' => $transactionStatus,
                'payment_status'     => in_array($transactionStatus, ['settlement', 'capture']) ? 'paid' : $transactionStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // HELPERS
    // =========================================================
    private function formatPayment(Payment $payment): array
    {
        $order = ($payment->relationLoaded('order') && $payment->order !== null)
            ? $payment->order
            : null;

        return [
            'id'                => $payment->id,
            'order_id'          => $payment->order_id,
            'survey_id'         => $payment->survey_id ?? null,
            'amount'            => $payment->amount,
            'method'            => $payment->method,
            'payment_channel'   => $payment->payment_channel,
            'reference_id'      => $payment->reference_id,
            'midtrans_order_id' => $payment->midtrans_order_id ?? null,
            'snap_token'        => $payment->snap_token ?? null,
            'status'            => $payment->status,
            'type'              => $payment->type ?? null,
            'paid_at'           => $payment->paid_at?->toDateTimeString(),
            'created_at'        => $payment->created_at->toDateTimeString(),
            'order'             => $order !== null ? [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'total_price'  => $order->total_price,
            ] : null,
        ];
    }

    private function formatSurveyPayment(Payment $payment, $survey): array
    {
        return [
            'id'                => $payment->id,
            'survey_id'         => $payment->survey_id,
            'amount'            => $payment->amount,
            'method'            => $payment->method,
            'payment_channel'   => $payment->payment_channel,
            'midtrans_order_id' => $payment->midtrans_order_id ?? null,
            'snap_token'        => $payment->snap_token ?? null,
            'status'            => $payment->status,
            'paid_at'           => $payment->paid_at?->toDateTimeString(),
            'survey'            => [
                'id'         => $survey->id,
                'status'     => $survey->status,
                'survey_fee' => $survey->survey_fee,
                'address'    => $survey->address,
            ],
        ];
    }

    // // =========================================================
    // // CREATE SNAP TOKEN — Midtrans Snap
    // // POST /api/customer/payments/snap
    // // =========================================================
    // public function createSnap(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'order_id' => 'required|exists:orders,id',
    //     ]);

    //     $order = Order::with('details')->find($request->order_id);

    //     if ($order->customer_id !== $request->user()->id) {
    //         return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
    //     }

    //     if (!in_array($order->status, ['pending', 'accepted'])) {
    //         return response()->json(['status' => false, 'message' => 'Order tidak bisa dibayar.'], 422);
    //     }

    //     // Cek snap token yang masih valid
    //     $existing = Payment::where('order_id', $order->id)
    //         ->where('status', 'pending')
    //         ->whereNotNull('snap_token')
    //         ->first();

    //     if ($existing) {
    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Snap token tersedia.',
    //             'data'    => [
    //                 'snap_token'   => $existing->snap_token,
    //                 'payment_id'   => $existing->id,
    //                 'order_id'     => $order->id,
    //                 'order_number' => $order->order_number,
    //                 'total_price'  => (float) $order->total_price,
    //             ],
    //         ]);
    //     }

    //     \Midtrans\Config::$serverKey    = config('midtrans.server_key');
    //     \Midtrans\Config::$isProduction = config('midtrans.is_production');
    //     \Midtrans\Config::$isSanitized  = config('midtrans.is_sanitized');
    //     \Midtrans\Config::$is3ds        = config('midtrans.is_3ds');

    //     $user            = $request->user();
    //     $midtransOrderId = 'ORDER-' . $order->order_number . '-' . time();
    //     // Build item_details dari order details
    //     $items = $order->details->map(fn($d) => [
    //         'id'       => (string) $d->id,
    //         'price'    => (int) ($d->price ?? 0),
    //         'quantity' => (int) $d->qty,
    //         'name'     => substr($d->service_name ?? 'Layanan', 0, 50),
    //     ])->toArray();

    //     // ✅ FIX: jika details kosong, gunakan subtotal sebagai 1 item
    //     if (empty($items)) {
    //         $items[] = [
    //             'id'       => 'subtotal',
    //             'price'    => (int) $order->subtotal,
    //             'quantity' => 1,
    //             'name'     => 'Jasa Pekerjaan',
    //         ];
    //     }

    //     // Tambah service fee
    //     if ($order->service_fee > 0) {
    //         $items[] = [
    //             'id'       => 'service-fee',
    //             'price'    => (int) $order->service_fee,
    //             'quantity' => 1,
    //             'name'     => 'Biaya Layanan Platform',
    //         ];
    //     }

    //     $params = [
    //         'transaction_details' => [
    //             'order_id'     => $midtransOrderId,
    //             'gross_amount' => (int) $order->total_price,
    //         ],
    //         'customer_details' => [
    //             'first_name' => $user->name,
    //             'email'      => $user->email,
    //             'phone'      => $user->phone ?? '',
    //         ],
    //         'item_details' => $items,
    //     ];

    //     try {
    //         $snapToken = \Midtrans\Snap::getSnapToken($params);

    //         $payment = Payment::updateOrCreate(
    //             ['order_id' => $order->id],
    //             [
    //                 'customer_id'       => $user->id,
    //                 'user_id'           => $user->id,
    //                 'amount'            => $order->total_price,
    //                 'method'            => 'transfer',
    //                 'status'            => 'pending',
    //                 'type'              => 'order',
    //                 'snap_token'        => $snapToken,
    //                 'midtrans_order_id' => $midtransOrderId,
    //             ]
    //         );

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Snap token berhasil dibuat.',
    //             'data'    => [
    //                 'snap_token'   => $snapToken,
    //                 'payment_id'   => $payment->id,
    //                 'order_id'     => $order->id,
    //                 'order_number' => $order->order_number,
    //                 'total_price'  => (float) $order->total_price,
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('createSnap error: ' . $e->getMessage());
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Gagal membuat snap token: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }


    // ────────────────────────────────────────────────────────
    // GANTI method createSnap yang lama dengan yang ini
    // ────────────────────────────────────────────────────────
    public function createSnap(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'       => 'required|integer',
            'payment_method' => 'nullable|string',
            'type'           => 'nullable|string',
        ]);

        $customer = Auth::user();

        // Nama relasi sesuai Order.php:
        //   details()        → bukan orderDetails
        //   surveyRequest()  → ada
        // Nama relasi sesuai SurveyRequest.php:
        //   surveyServices() → bukan services
        $order = Order::with([
            'tukang',
            'details',                          // ← fix: bukan orderDetails
            'surveyRequest.surveyServices',     // ← fix: bukan surveyRequest.services
        ])->findOrFail($request->order_id);

        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!in_array($order->status, ['pending', 'accepted'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak bisa dibayar dengan status: ' . $order->status,
            ], 422);
        }

        // Buat atau ambil payment pending
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id, 'status' => 'pending'],
            [
                'amount' => $order->total_price,
                'method' => $request->payment_method ?? 'midtrans',
                'type'   => $request->type ?? 'order',
            ]
        );

        // Kalau snap_token sudah ada dan bukan baru dibuat, return langsung
        if ($payment->snap_token && !$payment->wasRecentlyCreated) {
            return response()->json([
                'status'  => true,
                'message' => 'Snap token tersedia.',
                'data'    => $this->_buildResponseData($order, $payment, $payment->snap_token),
            ]);
        }

        // ── Midtrans config ───────────────────────────────────
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        $params = [
            'transaction_details' => [
                'order_id'     => 'PAY-' . $payment->id . '-' . time(),
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email'      => $customer->email,
                'phone'      => $customer->phone ?? '',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('createSnap Midtrans error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat snap token: ' . $e->getMessage(),
            ], 500);
        }

        $payment->update(['snap_token' => $snapToken]);

        return response()->json([
            'status'  => true,
            'message' => 'Snap token berhasil dibuat.',
            'data'    => $this->_buildResponseData($order, $payment, $snapToken),
        ]);
    }

    // ── Helper: bangun array data response ───────────────────
    private function _buildResponseData(Order $order, Payment $payment, string $snapToken): array
    {
        $tukangName  = $order->tukang?->name ?? '-';
        $address     = $order->address ?? '-';

        // Service name dari details (relasi details(), bukan orderDetails)
        $serviceName = '-';
        if ($order->relationLoaded('details') && $order->details->isNotEmpty()) {
            // Coba ambil dari relasi service di dalam detail
            // Sesuaikan field name dengan kolom di tabel order_details kamu
            $names = $order->details->map(function ($d) {
                // Coba beberapa kemungkinan nama field
                return $d->service?->name
                    ?? $d->service_name
                    ?? $d->name
                    ?? null;
            })->filter()->unique()->values();
            $serviceName = $names->implode(', ') ?: '-';
        }

        // Survey services dari surveyRequest.surveyServices
        $surveyServices = [];

        if (
            $order->relationLoaded('surveyRequest') &&
            $order->surveyRequest &&
            $order->surveyRequest->relationLoaded('surveyServices')
        ) {
            foreach ($order->surveyRequest->surveyServices as $ss) {
                $price    = (float) ($ss->price ?? 0);
                $qty      = (int)   ($ss->quantity ?? $ss->qty ?? 1);
                $subtotal = (float) ($ss->subtotal ?? ($price * $qty));

                // Nama service: coba dari relasi atau dari kolom langsung
                $ssName = $ss->service?->name
                    ?? $ss->service_name
                    ?? $ss->name
                    ?? '-';

                $surveyServices[] = [
                    'service_name' => $ssName,
                    'price'        => $price,
                    'quantity'     => $qty,
                    'subtotal'     => $subtotal,
                    'notes'        => $ss->notes ?? null,
                ];
            }
        }

        // Fallback: ambil dari details jika surveyServices kosong
        if (
            empty($surveyServices) &&
            $order->relationLoaded('details') &&
            $order->details->isNotEmpty()
        ) {
            foreach ($order->details as $d) {
                $price    = (float) ($d->unit_price ?? $d->price ?? 0);
                $qty      = (int)   ($d->quantity ?? $d->qty ?? 1);
                $subtotal = (float) ($d->subtotal ?? $d->total ?? ($price * $qty));
                $dName    = $d->service?->name
                    ?? $d->service_name
                    ?? $d->name
                    ?? '-';

                $surveyServices[] = [
                    'service_name' => $dName,
                    'price'        => $price,
                    'quantity'     => $qty,
                    'subtotal'     => $subtotal,
                    'notes'        => $d->notes ?? null,
                ];
            }
        }

        return [
            'id'              => $payment->id,
            'order_id'        => $order->id,
            'order_number'    => $order->order_number,
            'snap_token'      => $snapToken,
            'total_price'     => (float) $order->total_price,
            'subtotal'        => (float) ($order->subtotal ?? $order->total_price),
            'service_fee'     => (float) ($order->service_fee ?? 0),
            'tukang_name'     => $tukangName,
            'service_name'    => $serviceName,
            'address'         => $address,
            'survey_services' => $surveyServices,
            'status'          => $payment->status,
            'method'          => $payment->method ?? $payment->payment_method,
            'amount'          => (float) $payment->amount,
        ];
    }
}
