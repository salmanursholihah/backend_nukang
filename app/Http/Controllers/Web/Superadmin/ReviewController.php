<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with([
            'customer',
            'tukang',
            'order'
        ])->latest()->paginate(10);

        return view('pages.admin.reviews.index', compact('reviews'));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->get();
        $tukangs = User::where('role', 'tukang')->get();
        $orders = Order::all();

        return view('pages.admin.reviews.create', compact('customers', 'tukangs', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'customer_id' => 'required',
            'tukang_id' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable'
        ]);

        Review::create([
            'order_id' => $request->order_id,
            'customer_id' => $request->customer_id,
            'tukang_id' => $request->tukang_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_visible' => true
        ]);

        return redirect()->route('reviews.index')
            ->with('success', 'Review created successfully');
    }

    public function edit($id)
    {
        $review = Review::findOrFail($id);

        return view('pages.admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable',
            'is_visible' => 'required'
        ]);

        $review = Review::findOrFail($id);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_visible' => $request->is_visible
        ]);

        return redirect()->route('reviews.index')
            ->with('success', 'Review updated successfully');
    }

    public function destroy($id)
    {
        Review::findOrFail($id)->delete();

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully');
    }

    public function hide($id)
    {
        Review::findOrFail($id)->update([
            'is_visible' => false
        ]);

        return back()->with('success', 'Review hidden');
    }

    public function showReview($id)
    {
        Review::findOrFail($id)->update([
            'is_visible' => true
        ]);

        return back()->with('success', 'Review shown');
    }
}
