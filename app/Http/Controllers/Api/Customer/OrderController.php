<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['tukang.tukangProfile', 'details.service', 'progresses'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['tukang.tukangProfile', 'details.service', 'progresses'])
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tukang_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'address' => ['required', 'string'],
            'service_date' => ['required', 'date'],
            'qty' => ['required', 'integer','min:1'],
        ]);

        $service = \App\Models\Service::findOrFail($data['service_id']);

        $order = DB::transaction(function () use ($request, $data, $service) {

            $order = Order::create([
                'customer_id' => $request->user()->id,
                'tukang_id' => $data['tukang_id'],
                'total_price' => $service->price * $data['qty'],
                'service_date' => $data['service_date'],
                'address' => $data['address'],
                'status' => 'pending',
            ]);

            \App\Models\OrderDetail::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'price' => $service->price,
                'qty' => $data['qty'],
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'data' => $order->load(['details.service']),
        ], 201);
    }
}
