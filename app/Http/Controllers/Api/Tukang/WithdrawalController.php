<?php
// app/Http/Controllers/Api/Tukang/WithdrawalController.php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use App\Models\Withdrawal;
use App\Services\IrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    public function __construct(private IrisService $iris) {}

    // =========================================================================
    // INDEX — Riwayat penarikan milik tukang yang login
    // GET /api/tukang/withdrawals
    // =========================================================================
    public function index(Request $request): JsonResponse
    {
        $query = Withdrawal::where('tukang_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $withdrawals->total(),
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
            ],
            'data' => collect($withdrawals->items())
                ->map(fn($w) => $this->formatWithdrawal($w)),
        ]);
    }

    // =========================================================================
    // STORE — Ajukan penarikan via Midtrans Iris
    // POST /api/tukang/withdrawals
    //
    // Body: {
    //   amount, bank_name, bank_account_number, bank_account_name, notes?
    // }
    // =========================================================================
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount'              => 'required|numeric|min:50000',
            'bank_name'           => 'required|string|max:50',
            'bank_account_number' => 'required|string|max:30',
            'bank_account_name'   => 'required|string|max:100',
            'notes'               => 'nullable|string|max:255',
        ]);

        $tukangId = $request->user()->id;
        $user     = $request->user();

        // ── 1. Hitung saldo tersedia ──────────────────────────────────────────
        // Saldo yang bisa ditarik = total earning 'settled' - total withdrawal aktif
        $totalSettled = PartnerEarning::where('tukang_id', $tukangId)
            ->where('status', 'settled')
            ->sum('amount');

        $totalWithdrawn = Withdrawal::where('tukang_id', $tukangId)
            ->whereIn('status', ['pending', 'processing', 'success'])
            ->sum('amount');

        $availableBalance = $totalSettled - $totalWithdrawn;

        if ($request->amount > $availableBalance) {
            return response()->json([
                'status'  => false,
                'message' => 'Saldo tidak cukup. Saldo tersedia: Rp '
                    . number_format($availableBalance, 0, ',', '.'),
            ], 422);
        }

        // ── 2. Cek tidak ada withdrawal pending/processing ────────────────────
        $hasPending = Withdrawal::where('tukang_id', $tukangId)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($hasPending) {
            return response()->json([
                'status'  => false,
                'message' => 'Masih ada penarikan yang sedang diproses. '
                    . 'Tunggu hingga selesai sebelum mengajukan yang baru.',
            ], 422);
        }

        // ── 3. Cek saldo merchant Iris mencukupi ──────────────────────────────
        $irisBalance = $this->iris->getBalance();
        if ($irisBalance['success'] && $irisBalance['balance'] < $request->amount) {
            Log::warning('Iris merchant balance insufficient', [
                'iris_balance' => $irisBalance['balance'],
                'requested'    => $request->amount,
                'tukang_id'    => $tukangId,
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Sistem sedang tidak dapat memproses penarikan. '
                    . 'Silakan coba beberapa saat lagi atau hubungi admin.',
            ], 422);
        }

        // ── 4. Buat payout via Iris ───────────────────────────────────────────
        $referenceNo = 'WD-' . $tukangId . '-' . Str::upper(Str::random(8));

        $irisResult = $this->iris->createPayout(
            beneficiary: [
                'name'           => $request->bank_account_name,
                'account_number' => $request->bank_account_number,
                'bank'           => $request->bank_name,
                'email'          => $user->email ?? '',
                'amount'         => $request->amount,
                'notes'          => $request->input('notes', 'Withdrawal tukang'),
            ],
            referenceNo: $referenceNo,
        );

        // ── 5. Jika Iris gagal, jangan simpan ke DB ───────────────────────────
        if (!$irisResult['success']) {
            Log::warning('Iris createPayout failed', [
                'tukang_id' => $tukangId,
                'amount'    => $request->amount,
                'result'    => $irisResult,
            ]);
            return response()->json([
                'status'  => false,
                'message' => $irisResult['message']
                    ?? 'Gagal memproses penarikan via Midtrans Iris. Coba lagi.',
            ], 422);
        }

        // ── 6. Simpan ke database ─────────────────────────────────────────────
        DB::beginTransaction();
        try {
            $withdrawal = Withdrawal::create([
                'tukang_id'           => $tukangId,
                'amount'              => $request->amount,
                'bank_name'           => strtoupper($request->bank_name),
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name'   => $request->bank_account_name,
                'notes'               => $request->input('notes'),
                'status'              => 'processing', // langsung processing, bukan pending
                'reference_id'        => $referenceNo,
                'iris_reference_no'   => $irisResult['reference_no'] ?? $referenceNo,
                'iris_status'         => $irisResult['status'] ?? 'queued',
                'iris_response'       => $irisResult['data'] ?? [],
                'processed_at'        => null,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Permintaan penarikan berhasil diajukan dan sedang '
                    . 'diproses otomatis oleh Midtrans Iris.',
                'data'    => $this->formatWithdrawal($withdrawal),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WithdrawalController::store DB error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan data penarikan. Silakan coba lagi.',
            ], 500);
        }
    }

    // =========================================================================
    // SHOW — Detail + sync status dari Iris
    // GET /api/tukang/withdrawals/{withdrawal}
    // =========================================================================
    public function show(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        // Pastikan hanya pemiliknya yang bisa akses
        if ($withdrawal->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Data penarikan tidak ditemukan.',
            ], 404);
        }

        // Sync status dari Iris jika masih processing
        if ($withdrawal->isProcessing() && $withdrawal->iris_reference_no) {
            $this->syncIrisStatus($withdrawal);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatWithdrawal($withdrawal->fresh()),
        ]);
    }

    // =========================================================================
    // IRIS WEBHOOK — Notifikasi otomatis dari Midtrans Iris
    // POST /api/iris/webhook   (tanpa auth middleware)
    //
    // Set webhook URL di Iris dashboard:
    // Sandbox: https://app.sandbox.midtrans.com/iris > Settings > Notification URL
    // =========================================================================
    public function irisWebhook(Request $request): JsonResponse
    {
        Log::info('Iris webhook received', $request->all());

        $referenceNo = $request->input('reference_no');
        $irisStatus  = $request->input('status');

        if (!$referenceNo || !$irisStatus) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $withdrawal = Withdrawal::where('iris_reference_no', $referenceNo)->first();

        if (!$withdrawal) {
            Log::warning('Iris webhook: withdrawal not found', [
                'reference_no' => $referenceNo,
            ]);
            return response()->json(['message' => 'Withdrawal not found'], 404);
        }

        DB::beginTransaction();
        try {
            $newStatus = match ($irisStatus) {
                'processed' => 'success',
                'failed'    => 'failed',
                default     => 'processing',
            };

            $withdrawal->update([
                'iris_status'  => $irisStatus,
                'status'       => $newStatus,
                'processed_at' => in_array($irisStatus, ['processed', 'failed'])
                    ? now()
                    : $withdrawal->processed_at,
            ]);

            // Jika berhasil: tandai earning sebagai paid
            if ($newStatus === 'success') {
                PartnerEarning::where('tukang_id', $withdrawal->tukang_id)
                    ->where('status', 'settled')
                    ->update(['status' => 'paid']);

                Log::info('Iris webhook: earning updated to paid', [
                    'tukang_id'    => $withdrawal->tukang_id,
                    'reference_no' => $referenceNo,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Iris webhook processing error', [
                'message'      => $e->getMessage(),
                'reference_no' => $referenceNo,
            ]);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Sync status withdrawal dari Iris API (dipakai saat show() dipanggil)
     */
    private function syncIrisStatus(Withdrawal $withdrawal): void
    {
        if (!$withdrawal->iris_reference_no) return;

        $result = $this->iris->getPayoutDetail($withdrawal->iris_reference_no);
        if (!$result['success']) return;

        $irisStatus = $result['data']['status'] ?? null;
        if (!$irisStatus || $irisStatus === $withdrawal->iris_status) return;

        $newStatus = match ($irisStatus) {
            'processed' => 'success',
            'failed'    => 'failed',
            default     => 'processing',
        };

        $withdrawal->update([
            'iris_status'  => $irisStatus,
            'status'       => $newStatus,
            'processed_at' => in_array($irisStatus, ['processed', 'failed'])
                ? now()
                : $withdrawal->processed_at,
        ]);

        if ($newStatus === 'success') {
            PartnerEarning::where('tukang_id', $withdrawal->tukang_id)
                ->where('status', 'settled')
                ->update(['status' => 'paid']);
        }
    }

    /**
     * Format withdrawal untuk response JSON
     */
    private function formatWithdrawal(Withdrawal $w): array
    {
        return [
            'id'                  => $w->id,
            'amount'              => (float) $w->amount,
            'bank_name'           => $w->bank_name,
            'bank_account_number' => $w->bank_account_number,
            'bank_account_name'   => $w->bank_account_name,
            'reference_id'        => $w->reference_id,
            'iris_reference_no'   => $w->iris_reference_no,
            'iris_status'         => $w->iris_status,
            'iris_status_label'   => $w->iris_status_label,
            'status'              => $w->status,
            'status_label'        => $w->status_label,
            'notes'               => $w->notes,
            'processed_at'        => $w->processed_at?->toDateTimeString(),
            'created_at'          => $w->created_at->toDateTimeString(),
        ];
    }
}
///versi lama
// namespace App\Http\Controllers\Api\Tukang;

// use App\Http\Controllers\Controller;
// use App\Models\PartnerEarning;
// use App\Models\Withdrawal;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class WithdrawalController extends Controller
// {
//     // =========================================================
//     // INDEX — Riwayat penarikan saldo
//     // GET /api/tukang/withdrawals
//     // Query params:
//     //   ?status=pending|processing|success|failed
//     //   ?per_page=10
//     // =========================================================

//     public function index(Request $request): JsonResponse
//     {
//         $query = Withdrawal::where('tukang_id', $request->user()->id);

//         if ($request->filled('status')) {
//             $query->where('status', $request->status);
//         }

//         $withdrawals = $query->latest()->paginate($request->get('per_page', 10));

//         return response()->json([
//             'status' => true,
//             'meta'   => [
//                 'total'        => $withdrawals->total(),
//                 'current_page' => $withdrawals->currentPage(),
//                 'last_page'    => $withdrawals->lastPage(),
//             ],
//             'data' => collect($withdrawals->items())->map(fn($w) => $this->formatWithdrawal($w)),
//         ]);
//     }


//     // =========================================================
//     // STORE — Ajukan penarikan saldo
//     // POST /api/tukang/withdrawals
//     // Body:
//     //   amount               : decimal (required)
//     //   bank_name            : string (required) contoh: BCA, BNI, Mandiri
//     //   bank_account_number  : string (required)
//     //   bank_account_name    : string (required)
//     //   notes                : string (optional)
//     // =========================================================

//     public function store(Request $request): JsonResponse
//     {
//         $request->validate([
//             'amount'              => 'required|numeric|min:50000', // minimal withdraw 50rb
//             'bank_name'           => 'required|string|max:50',
//             'bank_account_number' => 'required|string|max:30',
//             'bank_account_name'   => 'required|string|max:100',
//             'notes'               => 'nullable|string|max:255',
//         ]);

//         $tukangId = $request->user()->id;

//         // Cek saldo tersedia
//         $totalSettled = PartnerEarning::where('tukang_id', $tukangId)
//             ->where('status', 'settled')
//             ->sum('amount');

//         $totalWithdrawn = Withdrawal::where('tukang_id', $tukangId)
//             ->whereIn('status', ['pending', 'processing', 'success'])
//             ->sum('amount');

//         $availableBalance = $totalSettled - $totalWithdrawn;

//         if ($request->amount > $availableBalance) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => "Saldo tidak cukup. Saldo tersedia: Rp " . number_format($availableBalance, 0, ',', '.'),
//             ], 422);
//         }

//         // Cek ada withdrawal pending/processing yang belum selesai
//         $hasPending = Withdrawal::where('tukang_id', $tukangId)
//             ->whereIn('status', ['pending', 'processing'])
//             ->exists();

//         if ($hasPending) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Kamu masih memiliki penarikan yang sedang diproses. Tunggu hingga selesai.',
//             ], 422);
//         }

//         DB::beginTransaction();
//         try {
//             $withdrawal = Withdrawal::create([
//                 'tukang_id'           => $tukangId,
//                 'amount'              => $request->amount,
//                 'bank_name'           => $request->bank_name,
//                 'bank_account_number' => $request->bank_account_number,
//                 'bank_account_name'   => $request->bank_account_name,
//                 'notes'               => $request->input('notes'),
//                 'status'              => 'pending',
//             ]);

//             DB::commit();

//             return response()->json([
//                 'status'  => true,
//                 'message' => 'Permintaan penarikan berhasil diajukan. Proses 1x24 jam kerja.',
//                 'data'    => $this->formatWithdrawal($withdrawal),
//             ], 201);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Gagal mengajukan penarikan. Silakan coba lagi.',
//                 'error'   => $e->getMessage(),
//             ], 500);
//         }
//     }


//     // =========================================================
//     // SHOW — Detail penarikan
//     // GET /api/tukang/withdrawals/{withdrawal}
//     // =========================================================

//     public function show(Request $request, Withdrawal $withdrawal): JsonResponse
//     {
//         if ($withdrawal->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Data penarikan tidak ditemukan.',
//             ], 404);
//         }

//         return response()->json([
//             'status' => true,
//             'data'   => $this->formatWithdrawal($withdrawal),
//         ]);
//     }


//     // =========================================================
//     // HELPERS
//     // =========================================================

//     private function formatWithdrawal(Withdrawal $withdrawal): array
//     {
//         return [
//             'id'                  => $withdrawal->id,
//             'amount'              => $withdrawal->amount,
//             'bank_name'           => $withdrawal->bank_name,
//             'bank_account_number' => $withdrawal->bank_account_number,
//             'bank_account_name'   => $withdrawal->bank_account_name,
//             'reference_id'        => $withdrawal->reference_id,
//             'status'              => $withdrawal->status,
//             'notes'               => $withdrawal->notes,
//             'processed_at'        => $withdrawal->processed_at?->toDateTimeString(),
//             'created_at'          => $withdrawal->created_at->toDateTimeString(),
//         ];
//     }
// }


