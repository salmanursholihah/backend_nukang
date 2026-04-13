<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
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

        $reviews = $query->latest()->paginate(15)->appends(request()->query());

        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $review->load([
            'customer:id,name,avatar',
            'tukang:id,name',
            'order:id,order_number,service_date',
        ]);

        return view('admin.reviews.show', compact('review'));
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review berhasil dihapus.');
    }

    public function unpublish(Review $review)
    {
        $review->update(['is_published' => ! $review->is_published]);

        $status = $review->is_published ? 'dipublish' : 'disembunyikan';

        return back()->with('success', "Review berhasil {$status}.");
    }
}
