<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer:id,name', 'tukang:id,name', 'payment:id,order_id,status']);

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

        $orders = $query->latest()->paginate(15)->appends(request()->query());

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'customer:id,name,phone,email,avatar',
            'tukang:id,name,phone',
            'tukang.tukangProfile:user_id,photo,rating',
            'details.service:id,name',
            'progresses',
            'payment',
            'review',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Order tidak bisa dibatalkan.');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        $order->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order berhasil dibatalkan.');
    }
}
