<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    use ImageUploadTrait;

    // GET /api/admin/categories
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('services');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $categories = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $categories->total(),
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
            ],
            'data' => collect($categories->items())->map(fn($c) => $this->formatCategory($c)),
        ]);
    }

    // GET /api/admin/categories/{category}
    public function show(Category $category): JsonResponse
    {
        $category->load(['services' => fn($q) => $q->orderBy('name')]);

        return response()->json([
            'status' => true,
            'data'   => array_merge($this->formatCategory($category), [
                'services' => $category->services->map(fn($s) => [
                    'id'         => $s->id,
                    'name'       => $s->name,
                    'base_price' => $s->base_price,
                    'is_active'  => $s->is_active,
                ]),
            ]),
        ]);
    }

    // POST /api/admin/categories
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:100|unique:categories,name',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'is_active' => 'sometimes|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'), 'categories');
        }

        $category = Category::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'image'     => $imagePath,
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Kategori berhasil dibuat.',
            'data'    => $this->formatCategory($category),
        ], 201);
    }

    // PUT /api/admin/categories/{category}
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name'      => 'sometimes|string|max:100|unique:categories,name,' . $category->id,
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only('name', 'is_active');

        if ($request->filled('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->replaceImage($request->file('image'), 'categories', $category->image);
        }

        $category->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Kategori berhasil diupdate.',
            'data'    => $this->formatCategory($category->fresh()),
        ]);
    }

    // DELETE /api/admin/categories/{category}
    public function destroy(Category $category): JsonResponse
    {
        if ($category->services()->count() > 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Kategori tidak bisa dihapus karena masih memiliki service.',
            ], 422);
        }

        $this->deleteImage($category->image);
        $category->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    private function formatCategory(Category $category): array
    {
        return [
            'id'             => $category->id,
            'name'           => $category->name,
            'slug'           => $category->slug,
            'image_url'      => $category->image ? asset($category->image) : null,
            'is_active'      => $category->is_active,
            'services_count' => $category->services_count ?? null,
            'created_at'     => $category->created_at->toDateTimeString(),
        ];
    }
}
