<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\TukangProfile;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
      public function index(Request $request)
    {
        $reviews = Review::with(['order', 'tukang'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $reviews,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $order = Order::where('customer_id', $request->user()->id)
            ->where('status', 'completed')
            ->findOrFail($data['order_id']);

        $existing = Review::where('order_id', $order->id)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Review untuk order ini sudah ada',
            ], 422);
        }

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $request->user()->id,
            'tukang_id' => $order->tukang_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        $avgRating = Review::where('tukang_id', $order->tukang_id)->avg('rating');

        TukangProfile::where('user_id', $order->tukang_id)->update([
            'rating' => round($avgRating, 2),
        ]);

        return response()->json([
            'message' => 'Review berhasil dikirim',
            'data' => $review,
        ], 201);
    }
}
