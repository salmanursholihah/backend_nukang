<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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
}
