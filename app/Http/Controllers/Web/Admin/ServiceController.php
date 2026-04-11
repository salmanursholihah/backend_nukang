<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use ImageUploadTrait;

    public function index(Request $request)
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

        $services = $query->latest()->paginate(15)->appends(request()->query());

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request)
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

        Service::create([
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'slug'           => Str::slug($request->name),
            'description'    => $request->description,
            'base_price'     => $request->input('base_price'),
            'price_per_unit' => $request->input('price_per_unit'),
            'unit'           => $request->input('unit'),
            'thumbnail'      => $thumbnailPath,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service berhasil ditambahkan.');
    }

    public function show(Service $service)
    {
        $service->load('category:id,name');
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $service->load('category:id,name');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:150|unique:services,name,' . $service->id,
            'description'    => 'nullable|string',
            'base_price'     => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'unit'           => 'nullable|string|max:30',
            'thumbnail'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'      => 'sometimes|boolean',
        ]);

        $data = [
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'slug'           => Str::slug($request->name),
            'description'    => $request->description,
            'base_price'     => $request->input('base_price'),
            'price_per_unit' => $request->input('price_per_unit'),
            'unit'           => $request->input('unit'),
            'is_active'      => $request->boolean('is_active'),
        ];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->replaceImage(
                $request->file('thumbnail'),
                'services',
                $service->thumbnail
            );
        }

        $service->update($data);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service berhasil diupdate.');
    }

    public function destroy(Service $service)
    {
        $this->deleteImage($service->thumbnail);
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service berhasil dihapus.');
    }
}
