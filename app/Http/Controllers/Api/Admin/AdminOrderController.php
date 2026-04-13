<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{

    // GET /api/admin/orders
    public function index(Request $request): JsonResponse
    {
        $query = Order::with([
            'customer:id,name,phone',
            'tukang:id,name',
            'payment:id,order_id,status,method',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
            'data' => collect($orders->items())->map(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'status'         => $o->status,
                'total_price'    => $o->total_price,
                'service_fee'    => $o->service_fee,
                'service_date'   => $o->service_date?->toDateTimeString(),
                'created_at'     => $o->created_at->toDateTimeString(),
                'customer'       => ['id' => $o->customer?->id, 'name' => $o->customer?->name, 'phone' => $o->customer?->phone],
                'tukang'         => ['id' => $o->tukang?->id,   'name' => $o->tukang?->name],
                'payment_status' => $o->payment?->status,
            ]),
        ]);
    }

    // GET /api/admin/orders/{order}
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'customer:id,name,phone,email',
            'tukang:id,name,phone,email',
            'tukang.tukangProfile:user_id,photo,rating',
            'details.service:id,name',
            'progresses',
            'payment',
            'review',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $order,
        ]);
    }

    // PUT /api/admin/orders/{order}/cancel
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak bisa dibatalkan.',
            ], 422);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        $order->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Order berhasil dibatalkan oleh admin.',
        ]);
    }
}
