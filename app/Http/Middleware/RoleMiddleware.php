<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah user sudah login
        if (! $request->user()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->route('login');
        }

        // Cek role
        if (! in_array($request->user()->role, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized. Akses ditolak.',
                ], 403);
            }
            abort(403, 'Unauthorized. Akses ditolak.');
        }

        // Cek akun aktif
        if (! $request->user()->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akun kamu tidak aktif. Hubungi admin.',
                ], 403);
            }
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun kamu tidak aktif. Hubungi admin.']);
        }

        return $next($request);
    }
}
