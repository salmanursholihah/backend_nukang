<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\TukangLocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TukangController extends Controller
{
    // =========================================================
    // INDEX — Cari tukang terdekat
    // GET /api/tukangs
    // Query params:
    //   ?lat=-7.7&lng=110.3          → koordinat customer (wajib)
    //   ?radius=10                   → radius km (default 10)
    //   ?service_id=1                → filter by service
    //   ?category_id=1               → filter by kategori
    //   ?min_rating=4                → filter rating minimal
    //   ?sort=distance|rating|jobs   → urutan (default: distance)
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'lat'        => 'required|numeric',
            'lng'        => 'required|numeric',
            'radius'     => 'sometimes|numeric|min:1|max:100',
            'service_id' => 'sometimes|exists:services,id',
            'category_id' => 'sometimes|exists:categories,id',
            'min_rating' => 'sometimes|numeric|min:1|max:5',
            'sort'       => 'sometimes|in:distance,rating,jobs',
        ]);

        $lat    = $request->lat;
        $lng    = $request->lng;
        $radius = $request->get('radius', 10);
        $sort   = $request->get('sort', 'distance');

        $query = TukangLocation::nearby($lat, $lng, $radius)
            ->join('users', 'users.id', '=', 'tukang_locations.tukang_id')
            ->join('tukang_profiles', 'tukang_profiles.user_id', '=', 'tukang_locations.tukang_id')
            ->where('users.is_active', true)
            ->where('tukang_profiles.is_available', true)
            ->select([
                'tukang_locations.tukang_id',
                'users.name',
                'tukang_profiles.photo',
                'tukang_profiles.city',
                'tukang_profiles.rating',
                'tukang_profiles.total_jobs',
                'tukang_profiles.total_reviews',
                'tukang_profiles.is_verified',
                'tukang_profiles.radius_km',
            ]);

        // Filter by service
        if ($request->filled('service_id')) {
            $query->join('tukang_services', 'tukang_services.tukang_id', '=', 'tukang_locations.tukang_id')
                ->where('tukang_services.service_id', $request->service_id);
        }

        // Filter by kategori
        if ($request->filled('category_id')) {
            $query->join('tukang_services as ts2', 'ts2.tukang_id', '=', 'tukang_locations.tukang_id')
                ->join('services as sv', 'sv.id', '=', 'ts2.service_id')
                ->where('sv.category_id', $request->category_id);
        }

        // Filter by rating minimal
        if ($request->filled('min_rating')) {
            $query->where('tukang_profiles.rating', '>=', $request->min_rating);
        }

        // Sorting
        match ($sort) {
            'rating'   => $query->orderByDesc('tukang_profiles.rating'),
            'jobs'     => $query->orderByDesc('tukang_profiles.total_jobs'),
            default    => $query->orderBy('distance_km'),
        };

        $tukangs = $query->get();

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'     => $tukangs->count(),
                'radius_km' => $radius,
                'lat'       => $lat,
                'lng'       => $lng,
            ],
            'data' => $tukangs->map(fn($t) => $this->formatTukangCard($t)),
        ]);
    }


    // =========================================================
    // SHOW — Detail profil tukang
    // GET /api/tukangs/{tukang}
    // Query params:
    //   ?lat=-7.7&lng=110.3  → tampilkan jarak dari customer
    // =========================================================

    public function show(Request $request, User $tukang): JsonResponse
    {
        // Pastikan user ini adalah tukang
        if (! $tukang->isTukang()) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang tidak ditemukan.',
            ], 404);
        }

        if (! $tukang->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang tidak tersedia.',
            ], 404);
        }

        $tukang->load([
            'tukangProfile',
            'tukangLocation',
            'tukangServices.category',
        ]);

        $profile  = $tukang->tukangProfile;
        $location = $tukang->tukangLocation;

        // Hitung jarak jika ada koordinat
        $distanceKm = null;
        if ($request->filled('lat') && $request->filled('lng') && $location) {
            $distanceKm = $this->calculateDistance(
                $request->lat,
                $request->lng,
                $location->latitude,
                $location->longitude
            );
        }

        // Ambil 5 review terbaru
        $latestReviews = Review::with('customer:id,name,avatar')
            ->where('tukang_id', $tukang->id)
            ->where('is_published', true)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($r) => $this->formatReview($r));

        return response()->json([
            'status' => true,
            'data'   => [
                'id'            => $tukang->id,
                'name'          => $tukang->name,
                'avatar_url'    => $tukang->avatar ? asset($tukang->avatar) : null,
                'is_online'     => $location?->is_online ?? false,
                'distance_km'   => $distanceKm ? round($distanceKm, 2) : null,
                'profile'       => $profile ? [
                    'city'          => $profile->city,
                    'province'      => $profile->province,
                    'bio'           => $profile->bio,
                    'photo_url'     => $profile->photo ? asset($profile->photo) : null,
                    'rating'        => $profile->rating,
                    'total_jobs'    => $profile->total_jobs,
                    'total_reviews' => $profile->total_reviews,
                    'is_verified'   => $profile->is_verified,
                    'is_available'  => $profile->is_available,
                    'radius_km'     => $profile->radius_km,
                ] : null,
                'services'      => $tukang->tukangServices->map(fn($s) => [
                    'id'           => $s->id,
                    'name'         => $s->name,
                    'category'     => $s->category?->name,
                    'custom_price' => $s->pivot->custom_price,
                    'base_price'   => $s->base_price,
                    'unit'         => $s->unit,
                ]),
                'latest_reviews' => $latestReviews,
            ],
        ]);
    }


    // =========================================================
    // REVIEWS — Semua review tukang (dengan pagination)
    // GET /api/tukangs/{tukang}/reviews
    // Query params:
    //   ?rating=5   → filter by bintang
    //   ?per_page=10
    // =========================================================

    public function reviews(Request $request, User $tukang): JsonResponse
    {
        if (! $tukang->isTukang()) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang tidak ditemukan.',
            ], 404);
        }

        $query = Review::with('customer:id,name,avatar')
            ->where('tukang_id', $tukang->id)
            ->where('is_published', true);

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $reviews->total(),
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                // Ringkasan rating
                'rating_summary' => $this->getRatingSummary($tukang->id),
            ],
            'data' => collect($reviews->items())->map(fn($r) => $this->formatReview($r)),
        ]);
    }


    // =========================================================
    // SERVICES — Daftar service yang dikuasai tukang
    // GET /api/tukangs/{tukang}/services
    // =========================================================

    public function services(User $tukang): JsonResponse
    {
        if (! $tukang->isTukang()) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang tidak ditemukan.',
            ], 404);
        }

        $tukang->load('tukangServices.category');

        return response()->json([
            'status' => true,
            'data'   => $tukang->tukangServices->map(fn($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'slug'         => $s->slug,
                'description'  => $s->description,
                'category'     => [
                    'id'   => $s->category?->id,
                    'name' => $s->category?->name,
                ],
                'base_price'   => $s->base_price,
                'custom_price' => $s->pivot->custom_price, // harga custom tukang ini
                'unit'         => $s->unit,
                'thumbnail_url' => $s->thumbnail ? asset($s->thumbnail) : null,
            ]),
        ]);
    }


    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function formatTukangCard($tukang): array
    {
        return [
            'id'          => $tukang->tukang_id,
            'name'        => $tukang->name,
            'photo_url'   => $tukang->photo ? asset($tukang->photo) : null,
            'city'        => $tukang->city,
            'rating'      => $tukang->rating,
            'total_jobs'  => $tukang->total_jobs,
            'total_reviews' => $tukang->total_reviews,
            'is_verified' => (bool) $tukang->is_verified,
            'distance_km' => round($tukang->distance_km, 2),
        ];
    }

    private function formatReview($review): array
    {
        return [
            'id'         => $review->id,
            'rating'     => $review->rating,
            'comment'    => $review->comment,
            'tags'       => $review->tags,
            'created_at' => $review->created_at->diffForHumans(),
            'customer'   => [
                'id'        => $review->customer?->id,
                'name'      => $review->customer?->name,
                'avatar_url' => $review->customer?->avatar
                    ? asset($review->customer->avatar)
                    : null,
            ],
        ];
    }

    private function getRatingSummary(int $tukangId): array
    {
        $reviews = Review::where('tukang_id', $tukangId)
            ->where('is_published', true)
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $summary = [];
        for ($i = 5; $i >= 1; $i--) {
            $summary[$i] = $reviews[$i] ?? 0;
        }

        return $summary;
    }

    /**
     * Hitung jarak dua koordinat pakai formula Haversine
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
