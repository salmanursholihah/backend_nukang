<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEarningController extends Controller
{
    // GET /api/admin/earnings
    public function index(Request $request): JsonResponse
    {
        $query = PartnerEarning::with([
            'tukang:id,name',
            'order:id,order_number,completed_at',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tukang_id')) {
            $query->where('tukang_id', $request->tukang_id);
        }

        $earnings = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $earnings->total(),
                'current_page' => $earnings->currentPage(),
                'last_page'    => $earnings->lastPage(),
            ],
            'data' => collect($earnings->items())->map(fn($e) => [
                'id'           => $e->id,
                'order_amount' => $e->order_amount,
                'platform_fee' => $e->platform_fee,
                'amount'       => $e->amount,
                'status'       => $e->status,
                'settled_at'   => $e->settled_at?->toDateTimeString(),
                'created_at'   => $e->created_at->toDateTimeString(),
                'tukang'       => ['id' => $e->tukang?->id, 'name' => $e->tukang?->name],
                'order'        => ['id' => $e->order?->id,  'order_number' => $e->order?->order_number],
            ]),
        ]);
    }

    // GET /api/admin/earnings/{earning}
    public function show(PartnerEarning $earning): JsonResponse
    {
        $earning->load(['tukang:id,name,phone', 'order:id,order_number,total_price,completed_at']);

        return response()->json([
            'status' => true,
            'data'   => $earning,
        ]);
    }

    // PUT /api/admin/earnings/{earning}/settle
    // Ubah status pending → settled (siap dicairkan tukang)
    public function settle(PartnerEarning $earning): JsonResponse
    {
        if ($earning->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya earning dengan status pending yang bisa di-settle.',
            ], 422);
        }

        $earning->update([
            'status'     => 'settled',
            'settled_at' => now(),
        ]);

        NotificationHelper::earningSettled($earning, $earning->amount);

        return response()->json([
            'status'  => true,
            'message' => 'Earning berhasil di-settle. Tukang sudah bisa mencairkan.',
        ]);
    }
}
