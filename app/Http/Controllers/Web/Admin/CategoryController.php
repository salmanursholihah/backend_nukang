<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ImageUploadTrait;

    public function index(Request $request)
    {
        $query = Category::withCount('services');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $categories = $query->latest()->paginate(15)->appends(request()->query());

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100|unique:categories,name',
            'icon'      => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'is_active' => 'sometimes|boolean',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $iconPath = $this->uploadImage($request->file('icon'), 'categories');
        }

        Category::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'icon'      => $iconPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category)
    {
        $category->load(['services' => fn($q) => $q->orderBy('name')]);
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'      => 'required|string|max:100|unique:categories,name,' . $category->id,
            'icon'      => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = [
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->replaceImage($request->file('icon'), 'categories', $category->icon);
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(Category $category)
    {
        if ($category->services()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki service.');
        }

        $this->deleteImage($category->icon);
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
