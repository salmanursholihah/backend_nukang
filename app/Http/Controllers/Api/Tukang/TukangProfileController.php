<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TukangProfileController extends Controller
{
    use ImageUploadTrait;

    // =========================================================
    // SHOW — Lihat profil tukang sendiri
    // GET /api/tukang/profile
    // =========================================================

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load([
            'tukangProfile',
            'tukangLocation',
            'tukangServices.category',
        ]);

        $profile  = $user->tukangProfile;
        $location = $user->tukangLocation;

        return response()->json([
            'status' => true,
            'data'   => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'avatar_url'  => $user->avatar ? asset($user->avatar) : null,
                'profile'     => $profile ? [
                    'address'       => $profile->address,
                    'city'          => $profile->city,
                    'province'      => $profile->province,
                    'bio'           => $profile->bio,
                    'photo_url'     => $profile->photo       ? asset($profile->photo)          : null,
                    'id_card_url'   => $profile->id_card_photo ? asset($profile->id_card_photo) : null,
                    'rating'        => $profile->rating,
                    'total_jobs'    => $profile->total_jobs,
                    'total_reviews' => $profile->total_reviews,
                    'is_verified'   => $profile->is_verified,
                    'is_available'  => $profile->is_available,
                    'radius_km'     => $profile->radius_km,
                    'latitude'      => $profile->latitude,
                    'longitude'     => $profile->longitude,
                ] : null,
                'location'    => $location ? [
                    'latitude'    => $location->latitude,
                    'longitude'   => $location->longitude,
                    'is_online'   => $location->is_online,
                    'last_seen_at' => $location->last_seen_at?->toDateTimeString(),
                ] : null,
                'services'    => $user->tukangServices->map(fn($s) => [
                    'id'           => $s->id,
                    'name'         => $s->name,
                    'category'     => $s->category?->name,
                    'thumbnail_url' => $s->thumbnail ? asset($s->thumbnail) : null,
                    'base_price'   => $s->base_price,
                    'custom_price' => $s->pivot->custom_price,
                    'unit'         => $s->unit,
                ]),
            ],
        ]);
    }


    // =========================================================
    // UPDATE — Update profil tukang
    // PUT /api/tukang/profile
    // Body:
    //   name      : string (optional)
    //   phone     : string (optional)
    //   address   : string (optional)
    //   city      : string (optional)
    //   province  : string (optional)
    //   bio       : string (optional)
    //   radius_km : numeric (optional)
    //   latitude  : numeric (optional)
    //   longitude : numeric (optional)
    // =========================================================

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'phone'     => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'address'   => 'sometimes|string',
            'city'      => 'sometimes|string|max:100',
            'province'  => 'sometimes|string|max:100',
            'bio'       => 'sometimes|string|max:500',
            'radius_km' => 'sometimes|numeric|min:1|max:100',
            'latitude'  => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ]);

        // Update data user
        if ($request->hasAny(['name', 'phone'])) {
            $user->update($request->only('name', 'phone'));
        }

        // Update tukang profile
        $profileData = $request->only('address', 'city', 'province', 'bio', 'radius_km', 'latitude', 'longitude');
        if (! empty($profileData)) {
            $user->tukangProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        }

        $user->load(['tukangProfile', 'tukangLocation', 'tukangServices.category']);

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diupdate.',
            'data'    => $this->show($request)->getData(true)['data'],
        ]);
    }


    // =========================================================
    // UPDATE PHOTO — Upload foto profil tukang
    // POST /api/tukang/profile/photo
    // Body (multipart):
    //   photo : image (required) max 2MB
    // =========================================================

    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user    = $request->user();
        $profile = $user->tukangProfile;

        if (! $profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profil tukang tidak ditemukan.',
            ], 404);
        }

        // Hapus foto lama & upload baru ke public/images/tukang/
        $path = $this->replaceImage($request->file('photo'), 'tukang', $profile->photo);
        $profile->update(['photo' => $path]);

        return response()->json([
            'status'  => true,
            'message' => 'Foto profil berhasil diupdate.',
            'data'    => [
                'photo_url' => asset($path),
            ],
        ]);
    }


    // =========================================================
    // UPLOAD ID CARD — Upload foto KTP untuk verifikasi
    // POST /api/tukang/profile/id-card
    // Body (multipart):
    //   id_card : image (required) max 2MB
    // =========================================================

    public function uploadIdCard(Request $request): JsonResponse
    {
        $request->validate([
            'id_card' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user    = $request->user();
        $profile = $user->tukangProfile;

        if (! $profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profil tukang tidak ditemukan.',
            ], 404);
        }

        // Simpan ke public/images/tukang/
        $path = $this->replaceImage($request->file('id_card'), 'tukang', $profile->id_card_photo);
        $profile->update([
            'id_card_photo' => $path,
            'is_verified'   => false, // reset, tunggu admin verifikasi ulang
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'KTP berhasil diupload. Menunggu verifikasi admin.',
            'data'    => [
                'id_card_url' => asset($path),
            ],
        ]);
    }


    // =========================================================
    // SERVICES — Daftar service yang dikuasai tukang
    // GET /api/tukang/services
    // =========================================================

    public function services(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('tukangServices.category');

        return response()->json([
            'status' => true,
            'data'   => $user->tukangServices->map(fn($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'category'     => [
                    'id'   => $s->category?->id,
                    'name' => $s->category?->name,
                ],
                'base_price'   => $s->base_price,
                'custom_price' => $s->pivot->custom_price,
                'unit'         => $s->unit,
                'thumbnail_url' => $s->thumbnail ? asset($s->thumbnail) : null,
            ]),
        ]);
    }


    // =========================================================
    // ADD SERVICE — Tambah service yang bisa dikerjakan
    // POST /api/tukang/services
    // Body:
    //   service_id    : int (required)
    //   custom_price  : decimal (optional)
    //   notes         : string (optional)
    // =========================================================

    public function addService(Request $request): JsonResponse
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'custom_price' => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Cek sudah ada
        $exists = DB::table('tukang_services')
            ->where('tukang_id', $user->id)
            ->where('service_id', $request->service_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Service ini sudah ada di daftar keahlian kamu.',
            ], 422);
        }

        DB::table('tukang_services')->insert([
            'tukang_id'    => $user->id,
            'service_id'   => $request->service_id,
            'custom_price' => $request->input('custom_price'),
            'notes'        => $request->input('notes'),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $service = Service::with('category')->find($request->service_id);

        return response()->json([
            'status'  => true,
            'message' => 'Service berhasil ditambahkan ke keahlian kamu.',
            'data'    => [
                'id'           => $service->id,
                'name'         => $service->name,
                'category'     => $service->category?->name,
                'base_price'   => $service->base_price,
                'custom_price' => $request->input('custom_price'),
                'unit'         => $service->unit,
            ],
        ], 201);
    }


    // =========================================================
    // REMOVE SERVICE — Hapus service dari daftar keahlian
    // DELETE /api/tukang/services/{service}
    // =========================================================

    public function removeService(Request $request, Service $service): JsonResponse
    {
        $user = $request->user();

        $deleted = DB::table('tukang_services')
            ->where('tukang_id', $user->id)
            ->where('service_id', $service->id)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'status'  => false,
                'message' => 'Service tidak ditemukan di daftar keahlian kamu.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Service berhasil dihapus dari keahlian kamu.',
        ]);
    }
}
