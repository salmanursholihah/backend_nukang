<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
public function index()
    {
        $categories = Category::latest()->get();

        return view('admin.categories.index', compact('categories'));
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

        return back();
    }

    public function update(Request $request, $id)
    {
        Category::findOrFail($id)->update($request->all());

        return back();
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return back();
    }
}