<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminServiceController extends Controller
{
    use ImageUploadTrait;

    // GET /api/admin/services
    public function index(Request $request): JsonResponse
    {
        $query = Service::with('category:id,name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $services->total(),
                'current_page' => $services->currentPage(),
                'last_page'    => $services->lastPage(),
            ],
            'data' => collect($services->items())->map(fn($s) => $this->formatService($s)),
        ]);
    }

    // GET /api/admin/services/{service}
    public function show(Service $service): JsonResponse
    {
        $service->load('category:id,name,slug');

        return response()->json([
            'status' => true,
            'data'   => $this->formatService($service),
        ]);
    }

    // POST /api/admin/services
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:150|unique:services,name',
            'description'    => 'nullable|string',
            'base_price'     => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'unit'           => 'nullable|string|max:30',
            'thumbnail'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'      => 'sometimes|boolean',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $this->uploadImage($request->file('thumbnail'), 'services');
        }

        $service = Service::create([
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'slug'           => Str::slug($request->name),
            'description'    => $request->description,
            'base_price'     => $request->input('base_price'),
            'price_per_unit' => $request->input('price_per_unit'),
            'unit'           => $request->input('unit'),
            'thumbnail'      => $thumbnailPath,
            'is_active'      => $request->input('is_active', true),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Service berhasil dibuat.',
            'data'    => $this->formatService($service->load('category:id,name')),
        ], 201);
    }

    // PUT /api/admin/services/{service}
    public function update(Request $request, Service $service): JsonResponse
    {
        $request->validate([
            'category_id'    => 'sometimes|exists:categories,id',
            'name'           => 'sometimes|string|max:150|unique:services,name,' . $service->id,
            'description'    => 'nullable|string',
            'base_price'     => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'unit'           => 'nullable|string|max:30',
            'thumbnail'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'      => 'sometimes|boolean',
        ]);

        $data = $request->only(
            'category_id',
            'name',
            'description',
            'base_price',
            'price_per_unit',
            'unit',
            'is_active'
        );

        if ($request->filled('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->replaceImage(
                $request->file('thumbnail'),
                'services',
                $service->thumbnail
            );
        }

        $service->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Service berhasil diupdate.',
            'data'    => $this->formatService($service->fresh()->load('category:id,name')),
        ]);
    }

    // DELETE /api/admin/services/{service}
    public function destroy(Service $service): JsonResponse
    {
        $this->deleteImage($service->thumbnail);
        $service->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Service berhasil dihapus.',
        ]);
    }

    private function formatService(Service $service): array
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
            'is_active'      => $service->is_active,
            'category'       => $service->relationLoaded('category') ? [
                'id'   => $service->category->id,
                'name' => $service->category->name,
            ] : null,
            'created_at'     => $service->created_at->toDateTimeString(),
        ];
    }
}
