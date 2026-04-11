<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    // GET /api/admin/reviews
    public function index(Request $request): JsonResponse
    {
        $query = Review::with([
            'customer:id,name',
            'tukang:id,name',
            'order:id,order_number',
        ]);

        if ($request->filled('tukang_id')) {
            $query->where('tukang_id', $request->tukang_id);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', (bool) $request->is_published);
        }

        $reviews = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $reviews->total(),
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
            ],
            'data' => collect($reviews->items())->map(fn($r) => [
                'id'           => $r->id,
                'rating'       => $r->rating,
                'comment'      => $r->comment,
                'tags'         => $r->tags,
                'is_published' => $r->is_published,
                'created_at'   => $r->created_at->toDateTimeString(),
                'customer'     => ['id' => $r->customer?->id, 'name' => $r->customer?->name],
                'tukang'       => ['id' => $r->tukang?->id,   'name' => $r->tukang?->name],
                'order'        => ['id' => $r->order?->id,    'order_number' => $r->order?->order_number],
            ]),
        ]);
    }

    // GET /api/admin/reviews/{review}
    public function show(Review $review): JsonResponse
    {
        $review->load([
            'customer:id,name,avatar',
            'tukang:id,name',
            'order:id,order_number,service_date',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $review,
        ]);
    }

    // DELETE /api/admin/reviews/{review}
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Review berhasil dihapus.',
        ]);
    }

    // PUT /api/admin/reviews/{review}/unpublish
    public function unpublish(Review $review): JsonResponse
    {
        $review->update(['is_published' => ! $review->is_published]);

        $status = $review->is_published ? 'dipublish' : 'disembunyikan';

        return response()->json([
            'status'  => true,
            'message' => "Review berhasil {$status}.",
            'data'    => ['id' => $review->id, 'is_published' => $review->is_published],
        ]);
    }
}
