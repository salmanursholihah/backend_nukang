<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;    

class ReviewController extends Controller
{
     // =========================================================
    // INDEX — Daftar review yang pernah dibuat customer
    // GET /api/customer/reviews
    // =========================================================
 
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with([
                'tukang:id,name,avatar',
                'tukang.tukangProfile:user_id,photo',
                'order:id,order_number',
            ])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->paginate($request->get('per_page', 10));
 
        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $reviews->total(),
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
            ],
            'data' => collect($reviews->items())->map(fn($r) => $this->formatReview($r)),
        ]);
    }
 
 
    // =========================================================
    // STORE — Beri review setelah order selesai
    // POST /api/customer/reviews
    // Body:
    //   order_id  : int (required)
    //   rating    : int 1-5 (required)
    //   comment   : string (optional)
    //   tags      : array (optional) contoh: ["tepat waktu","rapi","ramah"]
    // =========================================================
 
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:500',
            'tags'     => 'nullable|array',
            'tags.*'   => 'string|max:50',
        ]);
 
        $order = Order::with('review')->find($request->order_id);
 
        // Pastikan order milik customer ini
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        // Hanya bisa review jika order sudah selesai
        if ($order->status !== 'completed') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya order yang sudah selesai yang bisa diberi review.',
            ], 422);
        }
 
        // Cek sudah pernah review order ini
        if ($order->review) {
            return response()->json([
                'status'  => false,
                'message' => 'Kamu sudah memberikan review untuk order ini.',
            ], 422);
        }
 
        $review = Review::create([
            'order_id'    => $order->id,
            'customer_id' => $request->user()->id,
            'tukang_id'   => $order->tukang_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'tags'        => $request->tags,
        ]);
 
        $review->load([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo',
            'order:id,order_number',
        ]);
 
        return response()->json([
            'status'  => true,
            'message' => 'Review berhasil dikirim. Terima kasih!',
            'data'    => $this->formatReview($review),
        ], 201);
    }
 
 
    // =========================================================
    // HELPERS
    // =========================================================
 
    private function formatReview(Review $review): array
    {
        return [
            'id'         => $review->id,
            'rating'     => $review->rating,
            'comment'    => $review->comment,
            'tags'       => $review->tags,
            'created_at' => $review->created_at->toDateTimeString(),
            'order'      => $review->relationLoaded('order') ? [
                'id'           => $review->order->id,
                'order_number' => $review->order->order_number,
            ] : null,
            'tukang'     => $review->relationLoaded('tukang') ? [
                'id'         => $review->tukang->id,
                'name'       => $review->tukang->name,
                'avatar_url' => $review->tukang->avatar
                    ? asset($review->tukang->avatar) : null,
                'photo_url'  => $review->tukang->tukangProfile?->photo
                    ? asset($review->tukang->tukangProfile->photo) : null,
            ] : null,
        ];
    }
}
