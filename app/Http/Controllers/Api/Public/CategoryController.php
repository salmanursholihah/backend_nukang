<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ImageUploadTrait;

    // =========================================================
    // INDEX
    // GET /api/categories
    // =========================================================

    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount('services') // hitung jumlah service per kategori
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => $this->formatCategory($cat));

        return response()->json([
            'status' => true,
            'data'   => $categories,
        ]);
    }

    // =========================================================
    // SHOW
    // GET /api/categories/{category}
    // =========================================================

    public function show(Category $category): JsonResponse
    {
        if (! $category->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        // Load services aktif di kategori ini
        $category->load(['services' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }]);

        return response()->json([
            'status' => true,
            'data'   => array_merge($this->formatCategory($category), [
                'services' => $category->services->map(fn($s) => $this->formatService($s)),
            ]),
        ]);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function formatCategory(Category $category): array
    {
        return [
            'id'             => $category->id,
            'name'           => $category->name,
            'slug'           => $category->slug,
            'icon_url'       => $category->icon ? asset($category->icon) : null,
            'services_count' => $category->services_count ?? null,
        ];
    }

    private function formatService($service): array
    {
        return [
            'id'             => $service->id,
            'name'           => $service->name,
            'slug'           => $service->slug,
            'description'    => $service->description,
            'base_price'     => $service->base_price,
            'price_per_unit' => $service->price_per_unit,
            'unit'           => $service->unit,
            'thumbnail_url'  => $service->thumbnail ? asset($service->thumbnail) : null,
        ];
    }
}
