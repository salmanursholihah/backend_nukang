<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);

        $recentOrders = Order::with('customer')
            ->latest()
            ->paginate(5);

        return view('pages.admin.categories.index', compact('categories', 'recentOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Category::create([
            'name' => $request->name,
            'icon' => $request->icon
        ]);

        return back()->with('success', 'Category created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Category::findOrFail($id)->update([
            'name' => $request->name,
            'icon' => $request->icon
        ]);

        return back()->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return back()->with('success', 'Category deleted successfully');
    }
}
