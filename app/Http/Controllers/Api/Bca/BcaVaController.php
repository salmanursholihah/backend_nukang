<?php

namespace App\Http\Controllers\Api\Bca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\BcaService;

class BcaVaController extends Controller
{
    public function token(Bcaservice $bca)
    {
        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode(
                    env('BCA_CLIENT_ID') . ':' . env('BCA_CLIENT_SECRET')
                )
            ])
            ->post('https://sandbox.bca.co.id/api/oauth/token', [
                'grant_type' => 'client_credentials'
            ]);

        // return $response->json();
        return $bca->getToken();
    }

    public function callback(Request $request)
    {
        Log::info('BCA CALLBACK DATA:', $request->all());

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success'
        ]);
    }
     public function createVa(BcaService $bca)
    {
        // 1. Ambil token
        $tokenData = $bca->getToken();
        $token = $tokenData['access_token'];

        // 2. Body request
        $data = [
            'amount' => '10000'
        ];

        $body = json_encode($data, JSON_UNESCAPED_SLASHES);

        // 3. Timestamp
        $timestamp = $bca->generateTimestamp();

        // 4. Signature
        $signature = $bca->generateSignature(
            'POST',
            '/va/bca-va',
            $token,
            $body,
            $timestamp
        );

        // 5. Request ke BCA
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-BCA-Key' => env('BCA_CLIENT_ID'),
            'X-BCA-Timestamp' => $timestamp,
            'X-BCA-Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->post('https://sandbox.bca.co.id/va/bca-va', $data);

        return $response->json();
    }
}
