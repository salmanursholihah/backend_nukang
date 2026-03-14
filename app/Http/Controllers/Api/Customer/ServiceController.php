<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('category')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function show($id)
    {
        $service = Service::with('category')->findOrFail($id);

        return response()->json([
            'data' => $service,
        ]);
    }
}
