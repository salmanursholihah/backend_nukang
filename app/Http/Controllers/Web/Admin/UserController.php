<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\TukangProfile;
use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ImageUploadTrait;

    // =========================================================
    // INDEX — Daftar semua user
    // GET /admin/users
    // =========================================================

    public function index(Request $request)
    {
        $query = User::with('tukangProfile:user_id,rating,total_jobs,is_verified,city');

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

        $users = $query->latest()->paginate(15)->appends(request()->query());

        return view('admin.users.index', compact('users'));
    }


    // =========================================================
    // CREATE — Form tambah user
    // GET /admin/users/create
    // =========================================================

    public function create()
    {
        return view('admin.users.create');
    }


    // =========================================================
    // STORE — Simpan user baru
    // POST /admin/users
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'role'     => 'required|in:admin,customer,tukang',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        if ($user->isTukang()) {
            TukangProfile::create(['user_id' => $user->id]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }


    // =========================================================
    // SHOW — Detail user
    // GET /admin/users/{user}
    // =========================================================

    public function show(User $user)
    {
        $user->load([
            'tukangProfile',
            'tukangLocation',
            'tukangServices.category',
        ]);

        // Statistik order
        $orderStats = null;
        if ($user->isCustomer()) {
            $orderStats = [
                'total'     => $user->orders()->count(),
                'completed' => $user->orders()->where('status', 'completed')->count(),
                'cancelled' => $user->orders()->where('status', 'cancelled')->count(),
                'spent'     => $user->orders()->where('status', 'completed')->sum('total_price'),
            ];
        } elseif ($user->isTukang()) {
            $orderStats = [
                'total'     => $user->jobOrders()->count(),
                'completed' => $user->jobOrders()->where('status', 'completed')->count(),
                'cancelled' => $user->jobOrders()->where('status', 'cancelled')->count(),
                'earned'    => $user->earnings()->sum('amount'),
            ];
        }

        return view('admin.users.show', compact('user', 'orderStats'));
    }


    // =========================================================
    // EDIT — Form edit user
    // GET /admin/users/{user}/edit
    // =========================================================

    public function edit(User $user)
    {
        $user->load('tukangProfile');
        return view('admin.users.edit', compact('user'));
    }


    // =========================================================
    // UPDATE — Update user
    // PUT /admin/users/{user}
    // =========================================================

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'required|string|max:20|unique:users,phone,' . $user->id,
            'role'     => 'required|in:admin,customer,tukang',
            'password' => 'nullable|string|min:8|confirmed',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only('name', 'email', 'phone', 'role');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->replaceImage($request->file('avatar'), 'profiles', $user->avatar);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Data user berhasil diupdate.');
    }


    // =========================================================
    // DESTROY — Hapus user (soft delete)
    // DELETE /admin/users/{user}
    // =========================================================

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }


    // =========================================================
    // TOGGLE — Aktif / Nonaktif user
    // PUT /admin/users/{user}/toggle
    // =========================================================

    public function toggle(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User berhasil {$status}.");
    }


    // =========================================================
    // VERIFY — Verifikasi tukang
    // PUT /admin/users/{user}/verify
    // =========================================================

    public function verify(User $user)
    {
        if (! $user->isTukang()) {
            return back()->with('error', 'User ini bukan tukang.');
        }

        $profile = $user->tukangProfile;

        if (! $profile) {
            return back()->with('error', 'Profil tukang tidak ditemukan.');
        }

        $profile->update(['is_verified' => ! $profile->is_verified]);

        $status = $profile->is_verified ? 'diverifikasi' : 'dibatalkan verifikasinya';

        return back()->with('success', "Tukang berhasil {$status}.");
    }
}
