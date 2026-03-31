<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
 public function index()
    {
        $reviews = Review::with([
            'customer',
            'tukang',
            'order'
        ])->latest()->get();

        return view('admin.reviews.index', compact('reviews'));
    }
}