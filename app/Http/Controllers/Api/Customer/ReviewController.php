<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ReviewMedia;

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

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:500',
            'tags'     => 'nullable|array',
            'tags.*'   => 'string|max:100',
            // FIX: validasi media sebagai array file
            'media'    => 'nullable|array|max:5',
            'media.*'  => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,mkv|max:51200',
        ]);

        $customerId = auth()->id();

        $order = Order::where('id', $request->order_id)
            ->where('customer_id', $customerId)
            ->where('status', 'completed')
            ->firstOrFail();

        if (Review::where('order_id', $order->id)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'Kamu sudah memberikan review untuk order ini.',
            ], 422);
        }

        $review = Review::create([
            'order_id'    => $order->id,
            'customer_id' => $customerId,
            'tukang_id'   => $order->tukang_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'tags'        => $request->tags ?? [],
        ]);

        // FIX: $request->file('media') sudah otomatis membaca media[]
        // sebagai array — tidak perlu loop manual dengan index
        $mediaFiles = $request->file('media') ?? [];

        // Debug log — hapus setelah konfirmasi berjalan
        \Log::info('[ReviewController] media count: ' . count($mediaFiles));
        \Log::info('[ReviewController] allFiles: ', $request->allFiles());

        foreach ($mediaFiles as $file) {
            if (!$file->isValid()) continue;

            $mime = $file->getMimeType() ?? '';
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';

            $path = $file->store('review_media', 'public');
            $url  = Storage::url($path);

            ReviewMedia::create([
                'review_id' => $review->id,
                'file_path' => $path,
                'file_url'  => $url,
                'type'      => $type,
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Review berhasil dikirim!',
            'data'    => $review->load('media'),
        ], 201);
    }
}
