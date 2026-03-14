<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProgress;
use App\Models\PartnerEarning;
use App\Models\TukangProfile;
use Illuminate\Http\Request;

class TukangOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['customer', 'details.service', 'progresses'])
            ->where('tukang_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['customer', 'details.service', 'progresses'])
            ->where('tukang_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function start(Request $request, $id)
    {
        $order = Order::where('tukang_id', $request->user()->id)
            ->where('status', 'accepted')
            ->findOrFail($id);

        $order->update([
            'status' => 'on_progress',
        ]);

        return response()->json([
            'message' => 'Pekerjaan dimulai',
            'data' => $order,
        ]);
    }

    public function storeProgress(Request $request, $id)
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'string'],
        ]);

        $order = Order::where('tukang_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'on_progress'])
            ->findOrFail($id);

        $progress = OrderProgress::create([
            'order_id' => $order->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        if ($order->status !== 'on_progress') {
            $order->update([
                'status' => 'on_progress',
            ]);
        }

        return response()->json([
            'message' => 'Progress berhasil ditambahkan',
            'data' => $progress,
        ], 201);
    }

    public function complete(Request $request, $id)
    {
        $order = Order::where('tukang_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'on_progress'])
            ->findOrFail($id);

        $order->update([
            'status' => 'completed',
        ]);

        TukangProfile::where('user_id', $request->user()->id)->increment('total_jobs');

        PartnerEarning::firstOrCreate(
            [
                'order_id' => $order->id,
                'tukang_id' => $request->user()->id,
            ],
            [
                'amount' => $order->total_price,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'message' => 'Pekerjaan selesai',
            'data' => $order,
        ]);
    }
}
