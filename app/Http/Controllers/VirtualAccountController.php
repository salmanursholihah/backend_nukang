<?php

namespace App\Http\Controllers;

use App\Exceptions\BcaException;
use App\Http\Requests\CreateVirtualAccountRequest;
use App\Jobs\ProcessBcaCallback; // FIX: import yang benar (bukan ProcessedBcaCallback)
use App\Models\VirtualAccount;
use App\Services\BcaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VirtualAccountController extends Controller
{
    public function __construct(private BcaService $bcaService)
    {
        // BcaService di-inject otomatis oleh Laravel
    }

    /**
     * Buat Virtual Account baru.
     */
    public function create(CreateVirtualAccountRequest $request): JsonResponse
    {
        // FIX: pakai Form Request saja, hapus $request->validate() manual yang duplikat
        $data = $request->validated();

        // Cegah double VA untuk order yang sama
        $existing = VirtualAccount::where('order_id', $data['order_id'])
            ->where('status', 'PENDING')
            ->first();

        if ($existing) {
            return response()->json([
                'message'    => 'VA untuk order ini sudah ada',
                'va_number'  => $existing->va_number,
                'amount'     => $existing->amount,
                'expired_at' => $existing->expired_at,
                'status'     => $existing->status,
            ]);
        }

        // Susun data VA
        $customerNo = str_pad($data['order_id'], 11, '0', STR_PAD_LEFT);
        $vaNumber   = $this->bcaService->getCorpId() . $customerNo; // FIX: pakai getter
        $expiredAt  = now()->addHours(24)->format('Y-m-d H:i:s');
        $amount     = number_format($data['amount'], 2, '.', '');

        try {
            // Kirim ke BCA
            $bcaResult = $this->bcaService->createVirtualAccount([
                'customer_no'  => $customerNo,
                'va_number'    => $vaNumber,
                'name'         => $data['name'],
                'email'        => $data['customer_email'] ?? '',
                'amount'       => $amount,
                'description'  => $data['description'] ?? 'Pembayaran Order #' . $data['order_id'],
                'expired_date' => $expiredAt,
            ]);

            // Simpan ke database
            $va = VirtualAccount::create([
                'order_id'       => $data['order_id'],
                'va_number'      => $vaNumber,
                'customer_name'  => $data['name'],
                'customer_email' => $data['customer_email'] ?? null,
                'amount'         => $data['amount'],
                'status'         => 'PENDING',
                'expired_at'     => $expiredAt,
                'bca_response'   => $bcaResult,
            ]);

            return response()->json([
                'message'    => 'Virtual Account berhasil dibuat',
                'va_number'  => $va->va_number,
                'amount'     => $va->amount,
                'expired_at' => $va->expired_at,
                'status'     => $va->status,
            ], 201);

        } catch (BcaException $e) {
            Log::error('BCA Error: ' . $e->getMessage(), $e->context());
            return response()->json(['message' => 'Gagal membuat VA, coba lagi'], 500);

        } catch (\Exception $e) {
            Log::error('[BCA] Create VA exception', [
                'order_id' => $data['order_id'],
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    /**
     * Cek status VA berdasarkan nomor VA.
     */
    public function status(string $vaNumber): JsonResponse
    {
        $va = VirtualAccount::where('va_number', $vaNumber)->firstOrFail();

        return response()->json([
            'va_number'  => $va->va_number,
            'order_id'   => $va->order_id,
            'amount'     => $va->amount,
            'status'     => $va->status,
            'paid_at'    => $va->paid_at,
            'expired_at' => $va->expired_at,
        ]);
    }

    /**
     * Menerima notifikasi pembayaran dari BCA.
     * WAJIB reply {"Status":"00"} secepat mungkin.
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('[BCA] Callback received', [
            'payload' => $payload,
            'ip'      => $request->ip(),
        ]);

        if (empty($payload['VirtualAccountNo'])) {
            Log::warning('[BCA] Callback missing VirtualAccountNo');
            return response()->json(['Status' => '14', 'Desc' => 'Invalid request'], 400);
        }

        $va = VirtualAccount::where('va_number', $payload['VirtualAccountNo'])->first();

        if (!$va) {
            Log::warning('[BCA] Callback VA not found', [
                'va_number' => $payload['VirtualAccountNo'],
            ]);
            // Tetap reply 00 agar BCA tidak retry terus
            return response()->json(['Status' => '00', 'Desc' => 'Success']);
        }

        if ($va->isPaid()) {
            Log::info('[BCA] Callback already processed', [
                'va_number' => $va->va_number,
                'order_id'  => $va->order_id,
            ]);
            return response()->json(['Status' => '00', 'Desc' => 'Success']);
        }

        // FIX: dispatch ke job queue agar reply ke BCA cepat
        // Job yang handle update status ke PAID
        ProcessBcaCallback::dispatch($payload); // FIX: nama class yang benar

        Log::info('[BCA] Callback dispatched to queue', [
            'va_number' => $va->va_number,
            'order_id'  => $va->order_id,
        ]);

        // WAJIB reply ini ke BCA secepat mungkin
        return response()->json(['Status' => '00', 'Desc' => 'Success']);
    }
}
