<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TukangLocation;
use App\Models\TukangProfile;
use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ImageUploadTrait;

    // =========================================================
    // REGISTER
    // POST /api/register
    //
    // Mendukung role: customer, tukang, partner
    // "partner" adalah alias dari "tukang" — disimpan sebagai "tukang"
    // di DB agar backward-compatible dengan semua route & middleware.
    // Flutter cukup kirim role:"partner", backend simpan "tukang".
    // =========================================================

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'password' => ['required', 'confirmed', Password::min(8)],
            // Terima "partner" dari Flutter, tapi simpan sebagai "tukang"
            'role'     => 'required|in:customer,tukang,partner',
        ]);

        // Normalisasi: "partner" → "tukang"
        $role = $request->role === 'partner' ? 'tukang' : $request->role;

        $user = DB::transaction(function () use ($request, $role) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => $role,
            ]);

            // Tukang/Partner → auto buat profile & location
            if ($user->role === 'tukang') {
                TukangProfile::create(['user_id' => $user->id]);
                TukangLocation::create([
                    'tukang_id' => $user->id,
                    'latitude'  => 0,
                    'longitude' => 0,
                    'is_online' => false,
                ]);
            }

            return $user;
        });

        $this->loadUserRelations($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil.',
            'data'    => [
                'user'  => $this->formatUser($user),
                'token' => $token,
            ],
        ], 201);
    }


    // =========================================================
    // LOGIN
    // POST /api/login
    //
    // Satu endpoint untuk semua role: customer, tukang/partner, admin.
    // Flutter membaca field "role" di response untuk routing dashboard:
    //   - "customer"  → CustomerDashboard
    //   - "tukang"    → PartnerDashboard  (tampilkan sebagai "partner" di UI)
    //   - "admin"     → AdminDashboard
    // =========================================================

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Akun kamu tidak aktif. Hubungi admin.',
            ], 403);
        }

        // Hapus semua token lama → single session per user
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->loadUserRelations($user);

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => $this->formatUser($user),
                'token' => $token,
            ],
        ]);
    }


    // =========================================================
    // LOGOUT
    // POST /api/logout
    // =========================================================

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout berhasil.',
        ]);
    }


    // =========================================================
    // ME — Get current authenticated user
    // GET /api/me
    // =========================================================

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->loadUserRelations($user);

        return response()->json([
            'status' => true,
            'data'   => $this->formatUser($user),
        ]);
    }


    // =========================================================
    // UPDATE PROFILE
    // PUT /api/me
    // =========================================================

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'phone'     => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            // Field khusus tukang/partner
            'address'   => 'sometimes|string',
            'city'      => 'sometimes|string|max:100',
            'province'  => 'sometimes|string|max:100',
            'bio'       => 'sometimes|string',
            'radius_km' => 'sometimes|numeric|min:1|max:50',
            'latitude'  => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ]);

        $user->update($request->only('name', 'phone'));

        if ($user->isTukang()) {
            $user->tukangProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only('address', 'city', 'province', 'bio', 'radius_km', 'latitude', 'longitude')
            );
        }

        $fresh = $user->fresh();
        $this->loadUserRelations($fresh);

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diupdate.',
            'data'    => $this->formatUser($fresh),
        ]);
    }


    // =========================================================
    // UPDATE PASSWORD
    // PUT /api/me/password
    // =========================================================

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete(); // Paksa login ulang

        return response()->json([
            'status'  => true,
            'message' => 'Password berhasil diubah. Silakan login ulang.',
        ]);
    }


    // =========================================================
    // UPDATE AVATAR
    // POST /api/me/avatar
    // =========================================================

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            $oldPath = public_path($user->avatar);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }

        $path = $this->uploadImage($request->file('avatar'), 'profiles');
        $user->update(['avatar' => $path]);

        return response()->json([
            'status'  => true,
            'message' => 'Avatar berhasil diupdate.',
            'data'    => [
                'avatar_url' => asset($path),
            ],
        ]);
    }


    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Load relasi yang diperlukan sesuai role user.
     */
    private function loadUserRelations(User $user): void
    {
        if ($user->isTukang()) {
            $user->load([
                'tukangProfile',
                'tukangLocation',
                'tukangServices.category', // sertakan kategori tiap service
            ]);
        }
    }

    /**
     * Format user untuk response API.
     *
     * Untuk tukang/partner, field "role" tetap "tukang" di DB,
     * tapi kita tambahkan "role_label" = "partner" agar Flutter
     * punya flag yang lebih semantik untuk UI tanpa breaking change.
     */
    private function formatUser(User $user): array
    {
        $data = [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'role'       => $user->role,                                    // "customer" | "tukang" | "admin"
            'role_label' => $user->isTukang() ? 'partner' : $user->role,   // label ramah untuk UI
            'is_active'  => $user->is_active,
            'avatar_url' => $user->avatar ? asset($user->avatar) : null,
            'created_at' => $user->created_at->toDateTimeString(),
        ];

        // ── Data khusus Partner/Tukang ──────────────────────────────────
        if ($user->isTukang() && $user->relationLoaded('tukangProfile')) {
            $profile  = $user->tukangProfile;
            $location = $user->tukangLocation;

            $data['partner_profile'] = $profile ? [
                'id'              => $profile->id,
                'bio'             => $profile->bio,
                'address'         => $profile->address,
                'city'            => $profile->city,
                'province'        => $profile->province,
                'radius_km'       => $profile->radius_km,
                'latitude'        => $profile->latitude,
                'longitude'       => $profile->longitude,
                'is_verified'     => (bool) ($profile->is_verified ?? false),
                'verified_at'     => $profile->verified_at ?? null,
                'rating'          => $profile->rating ?? 0,
                'total_reviews'   => $profile->total_reviews ?? 0,
                'total_jobs_done' => $profile->total_jobs_done ?? 0,
                'photo_url'       => $profile->photo ? asset($profile->photo) : null,
                'id_card_url'     => $profile->id_card_photo ? asset($profile->id_card_photo) : null,
            ] : null;

            // Backward compat: tukang_profile masih ada
            $data['tukang_profile'] = $data['partner_profile'];

            $data['partner_location'] = $location ? [
                'latitude'  => $location->latitude,
                'longitude' => $location->longitude,
                'is_online' => (bool) $location->is_online,
                'updated_at'=> $location->updated_at?->toDateTimeString(),
            ] : null;

            // Backward compat
            $data['tukang_location'] = $data['partner_location'];

            $data['partner_services'] = $user->tukangServices->map(fn ($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'category'     => $s->category?->name,
                'custom_price' => $s->pivot->custom_price,
                'notes'        => $s->pivot->notes,
            ])->toArray();

            // Backward compat
            $data['tukang_services'] = $data['partner_services'];
        }

        return $data;
    }


    // =========================================================
    // IMAGE HELPERS (dipakai juga oleh TukangProfileController)
    // =========================================================

    protected function uploadImage($file, string $folder): string
    {
        $directory = public_path("images/{$folder}");

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return "images/{$folder}/{$filename}";
    }

    protected function deleteImage(?string $path): void
    {
        if (! $path) return;

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\TukangLocation;
// use App\Models\TukangProfile;
// use App\Models\User;
// use App\Traits\ImageUploadTrait;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\Rules\Password;

// class AuthController extends Controller
// {

//     use ImageUploadTrait;

//     // =========================================================
//     // REGISTER
//     // POST /api/register
//     // =========================================================

//     public function register(Request $request): JsonResponse
//     {
//         $request->validate([
//             'name'     => 'required|string|max:255',
//             'email'    => 'required|email|unique:users,email',
//             'phone'    => 'required|string|max:20|unique:users,phone',
//             'password' => ['required', 'confirmed', Password::min(8)],
//             'role'     => 'required|in:customer,tukang',
//         ]);

//         $user = User::create([
//             'name'     => $request->name,
//             'email'    => $request->email,
//             'phone'    => $request->phone,
//             'password' => Hash::make($request->password),
//             'role'     => $request->role,
//         ]);

//         // Jika daftar sebagai tukang → auto buat tukang_profile & tukang_location
//         if ($user->role === 'tukang') {
//             TukangProfile::create(['user_id' => $user->id]);
//             TukangLocation::create([
//                 'tukang_id' => $user->id,
//                 'latitude'  => 0,
//                 'longitude' => 0,
//                 'is_online' => false,
//             ]);
//         }

//         $token = $user->createToken('auth_token')->plainTextToken;

//         return response()->json([
//             'status'  => true,
//             'message' => 'Registrasi berhasil.',
//             'data'    => [
//                 'user'  => $this->formatUser($user),
//                 'token' => $token,
//             ],
//         ], 201);
//     }


//     // =========================================================
//     // LOGIN
//     // POST /api/login
//     // =========================================================

//     public function login(Request $request): JsonResponse
//     {
//         $request->validate([
//             'email'    => 'required|email',
//             'password' => 'required|string',
//         ]);

//         $user = User::where('email', $request->email)->first();

//         if (! $user || ! Hash::check($request->password, $user->password)) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Email atau password salah.',
//             ], 401);
//         }

//         if (! $user->is_active) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Akun kamu tidak aktif. Hubungi admin.',
//             ], 403);
//         }

//         $user->tokens()->delete();
//         $token = $user->createToken('auth_token')->plainTextToken;
//         $this->loadUserRelations($user);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Login berhasil.',
//             'data'    => [
//                 'user'  => $this->formatUser($user),
//                 'token' => $token,
//             ],
//         ]);
//     }


//     // =========================================================
//     // LOGOUT
//     // POST /api/logout
//     // =========================================================

//     public function logout(Request $request): JsonResponse
//     {
//         $request->user()->currentAccessToken()->delete();

//         return response()->json([
//             'status'  => true,
//             'message' => 'Logout berhasil.',
//         ]);
//     }


//     // =========================================================
//     // ME
//     // GET /api/me
//     // =========================================================

//     public function me(Request $request): JsonResponse
//     {
//         $user = $request->user();
//         $this->loadUserRelations($user);

//         return response()->json([
//             'status' => true,
//             'data'   => $this->formatUser($user),
//         ]);
//     }


//     // =========================================================
//     // UPDATE PROFILE
//     // PUT /api/me
//     // =========================================================

//     public function updateProfile(Request $request): JsonResponse
//     {
//         $user = $request->user();

//         $request->validate([
//             'name'      => 'sometimes|string|max:255',
//             'phone'     => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
//             'address'   => 'sometimes|string',
//             'city'      => 'sometimes|string|max:100',
//             'province'  => 'sometimes|string|max:100',
//             'bio'       => 'sometimes|string',
//             'radius_km' => 'sometimes|numeric|min:1|max:50',
//             'latitude'  => 'sometimes|numeric',
//             'longitude' => 'sometimes|numeric',
//         ]);

//         $user->update($request->only('name', 'phone'));

//         if ($user->isTukang()) {
//             $user->tukangProfile()->updateOrCreate(
//                 ['user_id' => $user->id],
//                 $request->only('address', 'city', 'province', 'bio', 'radius_km', 'latitude', 'longitude')
//             );
//         }

//         $this->loadUserRelations($user->fresh());

//         return response()->json([
//             'status'  => true,
//             'message' => 'Profil berhasil diupdate.',
//             'data'    => $this->formatUser($user->fresh()),
//         ]);
//     }


//     // =========================================================
//     // UPDATE PASSWORD
//     // PUT /api/me/password
//     // =========================================================

//     public function updatePassword(Request $request): JsonResponse
//     {
//         $request->validate([
//             'current_password' => 'required|string',
//             'password'         => ['required', 'confirmed', Password::min(8)],
//         ]);

//         $user = $request->user();

//         if (! Hash::check($request->current_password, $user->password)) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Password lama tidak sesuai.',
//             ], 422);
//         }

//         $user->update(['password' => Hash::make($request->password)]);

//         // Semua token dihapus → wajib login ulang
//         $user->tokens()->delete();

//         return response()->json([
//             'status'  => true,
//             'message' => 'Password berhasil diubah. Silakan login ulang.',
//         ]);
//     }


//     // =========================================================
//     // UPDATE AVATAR
//     // POST /api/me/avatar
//     // =========================================================

//     public function updateAvatar(Request $request): JsonResponse
//     {
//         $request->validate([
//             'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
//         ]);

//         $user = $request->user();

//         // Hapus avatar lama jika ada
//         if ($user->avatar) {
//             $oldPath = public_path($user->avatar);
//             if (File::exists($oldPath)) {
//                 File::delete($oldPath);
//             }
//         }

//         // Simpan ke public/images/profiles/
//         $path = $this->uploadImage($request->file('avatar'), 'profiles');
//         $user->update(['avatar' => $path]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Avatar berhasil diupdate.',
//             'data'    => [
//                 'avatar_url' => asset($path),
//             ],
//         ]);
//     }


//     // =========================================================
//     // HELPERS — bisa dipakai controller lain via trait nanti
//     // =========================================================

//     /**
//      * Upload image ke public/images/{folder}/
//      * Folder otomatis dibuat jika belum ada.
//      *
//      * Contoh hasil:  "images/profiles/6643a1bc_1713456789.jpg"
//      *
//      * Cara akses URL: asset($path)  → http://domain.com/images/profiles/xxx.jpg
//      */
//     protected function uploadImage($file, string $folder): string
//     {
//         $directory = public_path("images/{$folder}");

//         // Buat folder otomatis jika belum ada
//         if (! File::exists($directory)) {
//             File::makeDirectory($directory, 0755, true);
//         }

//         $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
//         $file->move($directory, $filename);

//         return "images/{$folder}/{$filename}";
//     }

//     /**
//      * Hapus image dari public/
//      */
//     protected function deleteImage(?string $path): void
//     {
//         if (! $path) return;

//         $fullPath = public_path($path);
//         if (File::exists($fullPath)) {
//             File::delete($fullPath);
//         }
//     }

//     private function loadUserRelations(User $user): void
//     {
//         if ($user->isTukang()) {
//             $user->load(['tukangProfile', 'tukangLocation', 'tukangServices']);
//         }
//     }

//     private function formatUser(User $user): array
//     {
//         $data = [
//             'id'         => $user->id,
//             'name'       => $user->name,
//             'email'      => $user->email,
//             'phone'      => $user->phone,
//             'role'       => $user->role,
//             'is_active'  => $user->is_active,
//             'avatar_url' => $user->avatar ? asset($user->avatar) : null,
//             'created_at' => $user->created_at->toDateTimeString(),
//         ];

//         if ($user->isTukang() && $user->relationLoaded('tukangProfile')) {
//             $profile = $user->tukangProfile;
//             $data['tukang_profile'] = $profile ? array_merge($profile->toArray(), [
//                 'photo_url'   => $profile->photo          ? asset($profile->photo)          : null,
//                 'id_card_url' => $profile->id_card_photo  ? asset($profile->id_card_photo)  : null,
//             ]) : null;
//             $data['tukang_location'] = $user->tukangLocation;
//             $data['tukang_services'] = $user->tukangServices;
//         }

//         return $data;
//     }
// }
