<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use Illuminate\Http\Request;

class TukangEarningController extends Controller
{
    public function index(Request $request)
    {
        $earnings = PartnerEarning::with('order')
            ->where('tukang_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $earnings,
        ]);
    }

    public function summary(Request $request)
    {
        $query = PartnerEarning::where('tukang_id', $request->user()->id);

        return response()->json([
            'data' => [
                'total' => (clone $query)->sum('amount'),
                'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
                'paid' => (clone $query)->where('status', 'paid')->sum('amount'),
            ],
        ]);
    }
}
