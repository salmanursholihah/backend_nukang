<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    // =========================================================
    // INDEX — Riwayat penarikan saldo
    // GET /api/tukang/withdrawals
    // Query params:
    //   ?status=pending|processing|success|failed
    //   ?per_page=10
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = Withdrawal::where('tukang_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $withdrawals->total(),
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
            ],
            'data' => collect($withdrawals->items())->map(fn($w) => $this->formatWithdrawal($w)),
        ]);
    }


    // =========================================================
    // STORE — Ajukan penarikan saldo
    // POST /api/tukang/withdrawals
    // Body:
    //   amount               : decimal (required)
    //   bank_name            : string (required) contoh: BCA, BNI, Mandiri
    //   bank_account_number  : string (required)
    //   bank_account_name    : string (required)
    //   notes                : string (optional)
    // =========================================================

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount'              => 'required|numeric|min:50000', // minimal withdraw 50rb
            'bank_name'           => 'required|string|max:50',
            'bank_account_number' => 'required|string|max:30',
            'bank_account_name'   => 'required|string|max:100',
            'notes'               => 'nullable|string|max:255',
        ]);

        $tukangId = $request->user()->id;

        // Cek saldo tersedia
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
                'message' => "Saldo tidak cukup. Saldo tersedia: Rp " . number_format($availableBalance, 0, ',', '.'),
            ], 422);
        }

        // Cek ada withdrawal pending/processing yang belum selesai
        $hasPending = Withdrawal::where('tukang_id', $tukangId)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($hasPending) {
            return response()->json([
                'status'  => false,
                'message' => 'Kamu masih memiliki penarikan yang sedang diproses. Tunggu hingga selesai.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $withdrawal = Withdrawal::create([
                'tukang_id'           => $tukangId,
                'amount'              => $request->amount,
                'bank_name'           => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name'   => $request->bank_account_name,
                'notes'               => $request->input('notes'),
                'status'              => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Permintaan penarikan berhasil diajukan. Proses 1x24 jam kerja.',
                'data'    => $this->formatWithdrawal($withdrawal),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengajukan penarikan. Silakan coba lagi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    // =========================================================
    // SHOW — Detail penarikan
    // GET /api/tukang/withdrawals/{withdrawal}
    // =========================================================

    public function show(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Data penarikan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatWithdrawal($withdrawal),
        ]);
    }


    // =========================================================
    // HELPERS
    // =========================================================

    private function formatWithdrawal(Withdrawal $withdrawal): array
    {
        return [
            'id'                  => $withdrawal->id,
            'amount'              => $withdrawal->amount,
            'bank_name'           => $withdrawal->bank_name,
            'bank_account_number' => $withdrawal->bank_account_number,
            'bank_account_name'   => $withdrawal->bank_account_name,
            'reference_id'        => $withdrawal->reference_id,
            'status'              => $withdrawal->status,
            'notes'               => $withdrawal->notes,
            'processed_at'        => $withdrawal->processed_at?->toDateTimeString(),
            'created_at'          => $withdrawal->created_at->toDateTimeString(),
        ];
    }
}
