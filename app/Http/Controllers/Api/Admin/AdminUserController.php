<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // =========================================================
    // INDEX — Daftar semua user
    // GET /api/admin/users
    // Query params:
    //   ?role=admin|customer|tukang
    //   ?is_active=0|1
    //   ?is_verified=0|1   (khusus tukang)
    //   ?search=nama/email
    //   ?per_page=15
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = User::with('tukangProfile:user_id,rating,total_jobs,is_verified,is_available,city');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('is_verified')) {
            $query->whereHas(
                'tukangProfile',
                fn($q) =>
                $q->where('is_verified', (bool) $request->is_verified)
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
            );
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
            'data' => collect($users->items())->map(fn($u) => $this->formatUser($u)),
        ]);
    }


    // =========================================================
    // SHOW — Detail user
    // GET /api/admin/users/{user}
    // =========================================================

    public function show(User $user): JsonResponse
    {
        $user->load([
            'tukangProfile',
            'tukangLocation',
            'tukangServices.category',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $this->formatUserDetail($user),
        ]);
    }


    // =========================================================
    // UPDATE — Edit data user
    // PUT /api/admin/users/{user}
    // =========================================================

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'phone'    => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'role'     => 'sometimes|in:admin,customer,tukang',
            'password' => 'sometimes|string|min:8',
        ]);

        $data = $request->only('name', 'email', 'phone', 'role');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Data user berhasil diupdate.',
            'data'    => $this->formatUser($user->fresh()),
        ]);
    }


    // =========================================================
    // DESTROY — Hapus user (soft delete)
    // DELETE /api/admin/users/{user}
    // =========================================================

    public function destroy(User $user): JsonResponse
    {
        // Jangan hapus diri sendiri
        if ($user->id === request()->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak bisa menghapus akun sendiri.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status'  => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }


    // =========================================================
    // TOGGLE — Aktif/nonaktif user
    // PUT /api/admin/users/{user}/toggle
    // =========================================================

    public function toggle(User $user): JsonResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'status'  => true,
            'message' => "User berhasil {$status}.",
            'data'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'is_active' => $user->is_active,
            ],
        ]);
    }


    // =========================================================
    // VERIFY — Verifikasi tukang
    // PUT /api/admin/users/{user}/verify
    // =========================================================

    public function verify(User $user): JsonResponse
    {
        if (! $user->isTukang()) {
            return response()->json([
                'status'  => false,
                'message' => 'User ini bukan tukang.',
            ], 422);
        }

        $profile = $user->tukangProfile;

        if (! $profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profil tukang tidak ditemukan.',
            ], 404);
        }

        $profile->update(['is_verified' => ! $profile->is_verified]);

        $status = $profile->is_verified ? 'diverifikasi' : 'dibatalkan verifikasinya';

        return response()->json([
            'status'  => true,
            'message' => "Tukang berhasil {$status}.",
            'data'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'is_verified' => $profile->is_verified,
            ],
        ]);
    }


    // =========================================================
    // HELPERS
    // =========================================================

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'role'        => $user->role,
            'is_active'   => $user->is_active,
            'avatar_url'  => $user->avatar ? asset($user->avatar) : null,
            'created_at'  => $user->created_at->toDateTimeString(),
            'tukang_info' => $user->relationLoaded('tukangProfile') && $user->tukangProfile ? [
                'city'        => $user->tukangProfile->city,
                'rating'      => $user->tukangProfile->rating,
                'total_jobs'  => $user->tukangProfile->total_jobs,
                'is_verified' => $user->tukangProfile->is_verified,
                'is_available' => $user->tukangProfile->is_available,
            ] : null,
        ];
    }

    private function formatUserDetail(User $user): array
    {
        $data = $this->formatUser($user);

        if ($user->isTukang()) {
            $profile = $user->tukangProfile;
            $data['tukang_profile'] = $profile ? [
                'address'       => $profile->address,
                'city'          => $profile->city,
                'province'      => $profile->province,
                'bio'           => $profile->bio,
                'photo_url'     => $profile->photo         ? asset($profile->photo)         : null,
                'id_card_url'   => $profile->id_card_photo ? asset($profile->id_card_photo) : null,
                'rating'        => $profile->rating,
                'total_jobs'    => $profile->total_jobs,
                'total_reviews' => $profile->total_reviews,
                'is_verified'   => $profile->is_verified,
                'is_available'  => $profile->is_available,
                'radius_km'     => $profile->radius_km,
            ] : null;

            $data['tukang_location'] = $user->tukangLocation ? [
                'latitude'  => $user->tukangLocation->latitude,
                'longitude' => $user->tukangLocation->longitude,
                'is_online' => $user->tukangLocation->is_online,
            ] : null;

            $data['tukang_services'] = $user->relationLoaded('tukangServices')
                ? $user->tukangServices->map(fn($s) => [
                    'id'           => $s->id,
                    'name'         => $s->name,
                    'category'     => $s->category?->name,
                    'custom_price' => $s->pivot->custom_price,
                ]) : [];
        }

        return $data;
    }
}
