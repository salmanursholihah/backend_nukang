<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWithdrawalController extends Controller
{
    // GET /api/admin/withdrawals
    public function index(Request $request): JsonResponse
    {
        $query = Withdrawal::with('tukang:id,name,phone');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $withdrawals->total(),
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
            ],
            'data' => collect($withdrawals->items())->map(fn($w) => [
                'id'                  => $w->id,
                'amount'              => $w->amount,
                'bank_name'           => $w->bank_name,
                'bank_account_number' => $w->bank_account_number,
                'bank_account_name'   => $w->bank_account_name,
                'status'              => $w->status,
                'reference_id'        => $w->reference_id,
                'processed_at'        => $w->processed_at?->toDateTimeString(),
                'created_at'          => $w->created_at->toDateTimeString(),
                'tukang'              => ['id' => $w->tukang?->id, 'name' => $w->tukang?->name, 'phone' => $w->tukang?->phone],
            ]),
        ]);
    }

    // GET /api/admin/withdrawals/{withdrawal}
    public function show(Withdrawal $withdrawal): JsonResponse
    {
        $withdrawal->load('tukang:id,name,phone,email');

        return response()->json([
            'status' => true,
            'data'   => $withdrawal,
        ]);
    }

    // PUT /api/admin/withdrawals/{withdrawal}/approve
    public function approve(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya withdrawal pending yang bisa disetujui.',
            ], 422);
        }

        $request->validate([
            'reference_id' => 'nullable|string|max:100',
        ]);

        $withdrawal->update([
            'status'       => 'success',
            'reference_id' => $request->input('reference_id'),
            'processed_at' => now(),
        ]);

        // Update earning tukang → paid
        PartnerEarning::where('tukang_id', $withdrawal->tukang_id)
            ->where('status', 'settled')
            ->update(['status' => 'paid']);

        NotificationHelper::withdrawalProcessed($withdrawal, 'success');

        return response()->json([
            'status'  => true,
            'message' => 'Penarikan berhasil disetujui dan ditransfer.',
        ]);
    }

    // PUT /api/admin/withdrawals/{withdrawal}/reject
    public function reject(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya withdrawal pending yang bisa ditolak.',
            ], 422);
        }

        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $withdrawal->update([
            'status'       => 'failed',
            'notes'        => $request->notes,
            'processed_at' => now(),
        ]);

        NotificationHelper::withdrawalProcessed($withdrawal, 'failed');

        return response()->json([
            'status'  => true,
            'message' => 'Penarikan ditolak.',
        ]);
    }
}
