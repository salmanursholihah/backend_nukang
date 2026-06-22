<?php

namespace App\Http\Middleware;

use App\Services\BcaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyBcaCallback
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function __construct(private BcaService $bcaService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $receivedSignature = $request->header('X-BCA-Signature');
        $timestamp         = $request->header('X-BCA-Timestamp');

        // Jika tidak ada signature di header, tolak
        if (!$receivedSignature || !$timestamp) {
            Log::warning('[BCA] Callback missing signature headers', [
                'ip'      => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            abort(401, 'Missing BCA signature');
        }

        // Hitung ulang signature dari body yang diterima
        $body = $request->getContent();

        $expectedSignature = $this->bcaService->generateSignature(
            'POST',
            '/api/bca/callback',  // sesuaikan dengan route Anda
            '',                   // callback tidak pakai access token
            $body,
            $timestamp
        );

        // Bandingkan — gunakan hash_equals agar aman dari timing attack
        if (!hash_equals($expectedSignature, $receivedSignature)) {
            Log::warning('[BCA] Callback invalid signature', [
                'expected' => $expectedSignature,
                'received' => $receivedSignature,
                'ip'       => $request->ip(),
            ]);
            abort(401, 'Invalid BCA signature');
        }

        return $next($request);
    }
}

