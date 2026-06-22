<?php
// app/Services/IrisService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IrisService
{
    private string $baseUrl;
    private string $apiKey;
    private string $merchantKey;

    public function __construct()
    {
        $this->baseUrl      = config('midtrans.iris_base_url');
        $this->apiKey       = config('midtrans.iris_api_key');
        $this->merchantKey  = config('midtrans.iris_merchant_key');
    }

    // =========================================================================
    // PRIVATE — Headers untuk semua request ke Iris
    // =========================================================================
    private function headers(): array
    {
        return [
            // Iris menggunakan Basic Auth: apiKey sebagai username, password kosong
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    // =========================================================================
    // VALIDATE BANK ACCOUNT
    // Validasi nomor rekening sebelum payout (opsional tapi disarankan)
    // GET /account_validation?bank=bca&account=1234567890
    // =========================================================================
    public function validateBankAccount(string $bankName, string $accountNumber): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/account_validation", [
                    'bank'    => strtolower($bankName),
                    'account' => $accountNumber,
                ]);

            return [
                'success'       => $response->successful(),
                'account_name'  => $response->json()['account_name'] ?? null,
                'data'          => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('IrisService::validateBankAccount', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'data' => []];
        }
    }

    // =========================================================================
    // CREATE PAYOUT
    // Buat disbursement ke rekening bank tukang
    // POST /payouts
    //
    // $beneficiary = [
    //   'name'           => 'Budi Santoso',
    //   'account_number' => '1234567890',
    //   'bank'           => 'bca',        // lowercase
    //   'email'          => 'budi@mail.com',
    //   'amount'         => 250000,       // rupiah, integer
    //   'notes'          => 'Withdrawal',
    // ]
    // =========================================================================
    public function createPayout(array $beneficiary, string $referenceNo): array
    {
        try {
            $payload = [
                'payouts' => [
                    [
                        'beneficiary_name'    => $beneficiary['name'],
                        'beneficiary_account' => $beneficiary['account_number'],
                        'beneficiary_bank'    => strtolower($beneficiary['bank']),
                        'beneficiary_email'   => $beneficiary['email'] ?? '',
                        'amount'              => (string) (int) $beneficiary['amount'],
                        'notes'               => $beneficiary['notes'] ?? 'Withdrawal tukang',
                        'bank_account_id'     => $referenceNo,
                    ],
                ],
            ];

            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/payouts", $payload);

            Log::info('IrisService::createPayout response', [
                'status'       => $response->status(),
                'reference_no' => $referenceNo,
                'body'         => $response->json(),
            ]);

            if ($response->successful()) {
                $payouts = $response->json()['payouts'] ?? [];
                $first   = $payouts[0] ?? [];

                return [
                    'success'      => true,
                    'reference_no' => $first['reference_no'] ?? $referenceNo,
                    'status'       => $first['status'] ?? 'queued',
                    'data'         => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['error_message']
                    ?? $response->json()['message']
                    ?? 'Gagal membuat payout Iris',
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('IrisService::createPayout', [
                'message'      => $e->getMessage(),
                'reference_no' => $referenceNo,
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // APPROVE PAYOUT
    // Wajib dipanggil jika akun Iris menggunakan 2-step approval
    // POST /payouts/approve
    // $referenceNumbers = ['WD-001', 'WD-002']
    // =========================================================================
    public function approvePayout(array $referenceNumbers): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/payouts/approve", [
                    'reference_nos' => $referenceNumbers,
                    'otp'           => $this->merchantKey, // Approver PIN
                ]);

            Log::info('IrisService::approvePayout response', [
                'reference_nos' => $referenceNumbers,
                'status'        => $response->status(),
                'body'          => $response->json(),
            ]);

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('IrisService::approvePayout', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // GET PAYOUT DETAIL
    // Cek status payout berdasarkan reference_no
    // GET /payouts/{reference_no}
    // Response status: queued | processed | failed
    // =========================================================================
    public function getPayoutDetail(string $referenceNo): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payouts/{$referenceNo}");

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('IrisService::getPayoutDetail', [
                'message'      => $e->getMessage(),
                'reference_no' => $referenceNo,
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // GET BALANCE
    // Cek saldo merchant di Iris (pastikan cukup sebelum payout)
    // GET /balance
    // =========================================================================
    public function getBalance(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/balance");

            return [
                'success' => $response->successful(),
                'balance' => (int) ($response->json()['balance'] ?? 0),
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('IrisService::getBalance', [
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'balance' => 0];
        }
    }
}
