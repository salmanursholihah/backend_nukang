<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    // =========================================================
    // INDEX — Riwayat pendapatan tukang
    // GET /api/tukang/earnings
    // Query params:
    //   ?status=pending|settled|paid
    //   ?per_page=10
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = PartnerEarning::with('order:id,order_number,service_date,completed_at')
            ->where('tukang_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $earnings = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $earnings->total(),
                'current_page' => $earnings->currentPage(),
                'last_page'    => $earnings->lastPage(),
            ],
            'data' => collect($earnings->items())->map(fn($e) => $this->formatEarning($e)),
        ]);
    }


    // =========================================================
    // SUMMARY — Ringkasan saldo tukang
    // GET /api/tukang/earnings/summary
    // =========================================================

    public function summary(Request $request): JsonResponse
    {
        $tukangId = $request->user()->id;

        $totals = PartnerEarning::where('tukang_id', $tukangId)
            ->selectRaw('
                SUM(amount) as total_earned,
                SUM(CASE WHEN status = "pending"  THEN amount ELSE 0 END) as total_pending,
                SUM(CASE WHEN status = "settled"  THEN amount ELSE 0 END) as total_settled,
                SUM(CASE WHEN status = "paid"     THEN amount ELSE 0 END) as total_paid,
                SUM(platform_fee) as total_platform_fee,
                COUNT(*) as total_jobs
            ')
            ->first();

        // Saldo yang bisa dicairkan = settled (sudah selesai tapi belum withdraw)
        $withdrawn = \App\Models\Withdrawal::where('tukang_id', $tukangId)
            ->whereIn('status', ['pending', 'processing', 'success'])
            ->sum('amount');

        $availableBalance = ($totals->total_settled ?? 0) - $withdrawn;

        return response()->json([
            'status' => true,
            'data'   => [
                'available_balance' => max(0, $availableBalance), // saldo bisa dicairkan
                'total_earned'      => $totals->total_earned      ?? 0,
                'total_pending'     => $totals->total_pending     ?? 0, // menunggu order selesai
                'total_settled'     => $totals->total_settled     ?? 0, // siap dicairkan
                'total_paid'        => $totals->total_paid        ?? 0, // sudah dicairkan
                'total_platform_fee' => $totals->total_platform_fee ?? 0,
                'total_jobs'        => $totals->total_jobs        ?? 0,
            ],
        ]);
    }


    // =========================================================
    // HELPERS
    // =========================================================

    private function formatEarning(PartnerEarning $earning): array
    {
        return [
            'id'           => $earning->id,
            'order_amount' => $earning->order_amount,
            'platform_fee' => $earning->platform_fee,
            'amount'       => $earning->amount,
            'status'       => $earning->status,
            'settled_at'   => $earning->settled_at?->toDateTimeString(),
            'created_at'   => $earning->created_at->toDateTimeString(),
            'order'        => $earning->relationLoaded('order') ? [
                'id'           => $earning->order->id,
                'order_number' => $earning->order->order_number,
                'service_date' => $earning->order->service_date?->toDateTimeString(),
                'completed_at' => $earning->order->completed_at?->toDateTimeString(),
            ] : null,
        ];
    }
}
