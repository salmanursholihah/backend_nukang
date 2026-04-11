<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // =========================================================
    // SHOW LOGIN
    // GET /login
    // =========================================================

    public function showLogin()
    {
        // Jika sudah login → redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }


    // =========================================================
    // LOGIN
    // POST /login
    // =========================================================

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek role harus admin
        if (! $user->isAdmin()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akses ditolak. Hanya admin yang bisa masuk.']);
        }

        // Cek akun aktif
        if (! $user->is_active) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun kamu tidak aktif. Hubungi superadmin.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }


    // =========================================================
    // LOGOUT
    // POST /logout
    // =========================================================

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
