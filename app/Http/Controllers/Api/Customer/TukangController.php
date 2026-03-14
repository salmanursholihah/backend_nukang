<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TukangController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('tukangProfile')
            ->where('role', 'tukang');

        if ($request->has('verified')) {
            $query->whereHas('tukangProfile', function ($q) use ($request) {
                $q->where('is_verified', filter_var($request->verified, FILTER_VALIDATE_BOOLEAN));
            });
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function show($id)
    {
        $tukang = User::with('tukangProfile')
            ->where('role', 'tukang')
            ->findOrFail($id);

        return response()->json([
            'data' => $tukang,
        ]);
    }
}
