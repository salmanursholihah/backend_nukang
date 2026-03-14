<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
public function index()
    {
        return response()->json([
            'data' => Category::latest()->get(),
        ]);
    }

    public function show($id)
    {
        $category = Category::with('services')->findOrFail($id);

        return response()->json([
            'data' => $category,
        ]);
    }
    }
