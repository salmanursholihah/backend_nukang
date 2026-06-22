<?php

namespace App\Services;

use App\Exceptions\BcaException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BcaService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $apiKey;
    private string $apiSecret;
    private string $corpId;

    public function __construct()
    {
        $this->baseUrl      = config('bca.base_url');
        $this->clientId     = config('bca.client_id');
        $this->clientSecret = config('bca.client_secret');
        $this->apiKey       = config('bca.api_key');
        $this->apiSecret    = config('bca.api_secret');
        $this->corpId       = config('bca.corp_id', 'BCAAPI2016');

        if (!$this->baseUrl) {
            throw new \Exception('BCA base_url kosong');
        }
    }

    public function getCorpId(): string
    {
        return $this->corpId;
    }

    /**
     * Ambil OAuth access token dari BCA (SNAP).
     * Cache 3500 detik agar tidak request token tiap call.
     */
    public function getToken(): string
    {
        return Cache::remember('bca_access_token', 3500, function () {

            $timestamp = $this->generateTimestamp();
            $signature = $this->generateTokenSignature($timestamp);

            Log::info('[BCA] Requesting token', ['timestamp' => $timestamp]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json', // SNAP b2b pakai JSON, bukan form
                'X-CLIENT-KEY' => $this->clientId,
                'X-TIMESTAMP'  => $timestamp,
                'X-SIGNATURE'  => $signature,
            ])->post($this->baseUrl . '/openapi/v1.0/access-token/b2b', [
                'grantType' => 'client_credentials', // SNAP pakai camelCase
            ]);

            if (!$response->successful()) {
                Log::error('[BCA TOKEN ERROR]', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new BcaException('Token gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            Log::info('[BCA] Token berhasil', [
                'expires_in' => $response->json('expiresIn'),
            ]);

            return $response->json('accessToken'); // SNAP pakai camelCase
        });
    }

    /**
     * Generate timestamp format BCA SNAP:
     * YYYY-MM-DDTHH:mm:ss+07:00  (TANPA milliseconds)
     *
     * FIX: Versi sebelumnya pakai .000 milliseconds → menyebabkan error
     * {"responseCode":"4007301","responseMessage":"Invalid field format [X-TIMESTAMP]"}
     */
    public function generateTimestamp(): string
    {
        return now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        // Contoh output: 2026-04-11T14:30:00+07:00
    }

    /**
     * Generate RSA-SHA256 signature untuk request token (asymmetric).
     * StringToSign = ClientKey + "|" + Timestamp
     */
    private function generateTokenSignature(string $timestamp): string
    {
        $stringToSign = $this->clientId . '|' . $timestamp;

        $pemPath    = storage_path('app/private_key.pem');
        $privateKey = openssl_pkey_get_private(file_get_contents($pemPath));

        if (!$privateKey) {
            throw new \Exception('Private key invalid: ' . openssl_error_string());
        }

        $ok = openssl_sign($stringToSign, $rawSignature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$ok) {
            throw new \Exception('openssl_sign gagal: ' . openssl_error_string());
        }

        return base64_encode($rawSignature);
    }

    /**
     * Generate HMAC-SHA256 signature untuk request transaksi (symmetric).
     * StringToSign = METHOD:RelativeURL:AccessToken:lowercase(SHA256(body)):Timestamp
     */
    public function generateSignature(
        string $method,
        string $relativeUrl,
        string $accessToken,
        string $requestBody,
        string $timestamp
    ): string {
        $bodyHash = strtolower(hash('sha256', $requestBody));

        $stringToSign = implode(':', [
            strtoupper($method),
            $relativeUrl,
            $accessToken,
            $bodyHash,
            $timestamp,
        ]);

        Log::debug('[BCA] StringToSign', ['value' => $stringToSign]);

        return base64_encode(hash_hmac('sha256', $stringToSign, $this->apiSecret, true));
        // FIX: hash_hmac harus raw binary (true) lalu di-base64, bukan hex string
    }

    /**
     * Susun header standar BCA SNAP untuk request transaksi.
     */
    public function buildHeaders(
        string $method,
        string $relativeUrl,
        string $requestBody = ''
    ): array {
        $token     = $this->getToken();
        $timestamp = $this->generateTimestamp();
        $signature = $this->generateSignature(
            $method,
            $relativeUrl,
            $token,
            $requestBody,
            $timestamp
        );

        return [
            'Authorization'    => 'Bearer ' . $token,
            'Content-Type'     => 'application/json',
            'X-TIMESTAMP'      => $timestamp,
            'X-SIGNATURE'      => $signature,
            'X-PARTNER-ID'     => $this->clientId,
            'X-EXTERNAL-ID'    => (string) now()->timestamp, // harus unik per request
            'CHANNEL-ID'       => '95221',
        ];
    }

    /**
     * Buat Virtual Account baru di BCA SNAP.
     */
    public function createVirtualAccount(array $data): array
    {
        $relativeUrl = '/openapi/v1.0/transfer-va/create-va';

        $payload = json_encode([
            'partnerServiceId' => str_pad($this->corpId, 8, ' ', STR_PAD_LEFT),
            'customerNo'       => $data['customer_no'],
            'virtualAccountNo' => $data['va_number'],
            'virtualAccountName' => $data['name'],
            'virtualAccountEmail' => $data['email'] ?? '',
            'totalAmount'      => [
                'value'    => number_format($data['amount'], 2, '.', ''),
                'currency' => 'IDR',
            ],
            'expiredDate'      => $data['expired_date'], // format: 2026-04-11T23:59:59+07:00
            'additionalInfo'   => [
                'description' => $data['description'] ?? '',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $headers = $this->buildHeaders('POST', $relativeUrl, $payload);

        Log::info('[BCA] Create VA request', ['va_number' => $data['va_number']]);

        $response = Http::withHeaders($headers)
            ->withBody($payload, 'application/json')
            ->post($this->baseUrl . $relativeUrl);

        Log::info('[BCA] Create VA response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        if (!$response->successful()) {
            Log::error('[BCA] Create VA gagal', [
                'status'    => $response->status(),
                'response'  => $response->body(),
                'va_number' => $data['va_number'],
            ]);
            throw new BcaException('Gagal membuat Virtual Account', [
                'response_code' => $response->status(),
                'va_number'     => $data['va_number'],
            ]);
        }

        return $response->json();
    }
}
