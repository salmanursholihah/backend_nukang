<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\TukangLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // =========================================================
    // INDEX
    // GET /api/services
    // Query params:
    //   ?category_id=1        → filter by kategori
    //   ?search=cat           → search nama service
    //   ?lat=-7.7&lng=110.3   → tampilkan jumlah tukang terdekat
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = Service::with('category')
            ->where('is_active', true);

        // Filter by kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by nama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->orderBy('name')->get();

        // Jika ada koordinat → hitung tukang terdekat per service
        $nearbyCount = [];
        if ($request->filled('lat') && $request->filled('lng')) {
            $nearbyCount = $this->countNearbyTukangs(
                $request->lat,
                $request->lng,
                $services->pluck('id')->toArray()
            );
        }

        return response()->json([
            'status' => true,
            'data'   => $services->map(fn($s) => array_merge(
                $this->formatService($s),
                ['nearby_tukangs' => $nearbyCount[$s->id] ?? null]
            )),
        ]);
    }

    // =========================================================
    // SHOW
    // GET /api/services/{service}
    // Query params:
    //   ?lat=-7.7&lng=110.3&radius=10  → tampilkan tukang terdekat
    // =========================================================

    public function show(Request $request, Service $service): JsonResponse
    {
        if (! $service->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Service tidak ditemukan.',
            ], 404);
        }

        $service->load('category');

        $data = $this->formatService($service);
        $data['category'] = [
            'id'   => $service->category->id,
            'name' => $service->category->name,
            'slug' => $service->category->slug,
        ];

        // Jika ada koordinat → tampilkan tukang terdekat untuk service ini
        if ($request->filled('lat') && $request->filled('lng')) {
            $radius   = $request->get('radius', 10);
            $tukangs  = $this->getNearbyTukangs($service->id, $request->lat, $request->lng, $radius);
            $data['nearby_tukangs'] = $tukangs;
        }

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }

    // =========================================================
    // HELPERS
    // =========================================================

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
            'category_id'    => $service->category_id,
        ];
    }

    /**
     * Hitung jumlah tukang online terdekat per service_id
     */
    private function countNearbyTukangs(float $lat, float $lng, array $serviceIds): array
    {
        $result = [];

        foreach ($serviceIds as $serviceId) {
            $count = TukangLocation::nearby($lat, $lng, 10)
                ->join('tukang_services', 'tukang_services.tukang_id', '=', 'tukang_locations.tukang_id')
                ->where('tukang_services.service_id', $serviceId)
                ->count();

            $result[$serviceId] = $count;
        }

        return $result;
    }

    /**
     * Ambil list tukang terdekat untuk service tertentu
     */
    private function getNearbyTukangs(int $serviceId, float $lat, float $lng, float $radius): array
    {
        return TukangLocation::nearby($lat, $lng, $radius)
            ->join('tukang_services', 'tukang_services.tukang_id', '=', 'tukang_locations.tukang_id')
            ->join('users', 'users.id', '=', 'tukang_locations.tukang_id')
            ->join('tukang_profiles', 'tukang_profiles.user_id', '=', 'tukang_locations.tukang_id')
            ->where('tukang_services.service_id', $serviceId)
            ->where('tukang_profiles.is_available', true)
            ->where('tukang_profiles.is_verified', true)
            ->select([
                'tukang_locations.tukang_id',
                'users.name',
                'tukang_profiles.photo',
                'tukang_profiles.rating',
                'tukang_profiles.total_jobs',
                'tukang_services.custom_price',
            ])
            ->selectRaw('
                ( 6371 * acos(
                    cos(radians(?)) * cos(radians(tukang_locations.latitude))
                    * cos(radians(tukang_locations.longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(tukang_locations.latitude))
                )) AS distance_km
            ', [$lat, $lng, $lat])
            ->orderBy('distance_km')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'tukang_id'    => $t->tukang_id,
                'name'         => $t->name,
                'photo_url'    => $t->photo ? asset($t->photo) : null,
                'rating'       => $t->rating,
                'total_jobs'   => $t->total_jobs,
                'custom_price' => $t->custom_price,
                'distance_km'  => round($t->distance_km, 2),
            ])
            ->toArray();
    }
}
