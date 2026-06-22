<?php

// app/Http/Controllers/Web/Admin/WithdrawalController.php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use App\Models\Withdrawal;
use App\Services\IrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    public function __construct(private IrisService $iris) {}

    // =========================================================================
    // INDEX — Daftar semua withdrawal + saldo Iris
    // GET /admin/withdrawals
    // =========================================================================
    public function index(Request $request)
    {
        $query = Withdrawal::with('tukang:id,name,phone,email');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas(
                'tukang',
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
            );
        }

        $withdrawals   = $query->latest()->paginate(15)->appends($request->query());
        $pendingCount  = Withdrawal::where('status', 'pending')->count();
        $pendingAmount = Withdrawal::where('status', 'pending')->sum('amount');

        // Saldo merchant Iris
        $irisBalanceData = $this->iris->getBalance();
        $irisBalance     = $irisBalanceData['balance'] ?? 0;

        return view('admin.withdrawals.index', compact(
            'withdrawals',
            'pendingCount',
            'pendingAmount',
            'irisBalance'
        ));
    }

    // =========================================================================
    // SHOW — Detail withdrawal + sync status Iris
    // GET /admin/withdrawals/{withdrawal}
    // =========================================================================
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load('tukang:id,name,phone,email');
        $irisData = null;

        // Ambil detail terbaru dari Iris jika ada reference_no
        if ($withdrawal->iris_reference_no) {
            $detail = $this->iris->getPayoutDetail($withdrawal->iris_reference_no);
            if ($detail['success']) {
                $irisData = $detail['data'];
            }
        }

        return view('admin.withdrawals.show', compact('withdrawal', 'irisData'));
    }

    // =========================================================================
    // APPROVE — Manual kirim ke Iris (untuk withdrawal yang stuck di 'pending')
    // POST /admin/withdrawals/{withdrawal}/approve
    // =========================================================================
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        if (!$withdrawal->isPending()) {
            return back()->with(
                'error',
                'Hanya withdrawal berstatus pending yang bisa disetujui.'
            );
        }

        // Buat payout baru ke Iris
        $referenceNo = $withdrawal->reference_id
            ?? 'WD-MANUAL-' . $withdrawal->id . '-' . time();

        $result = $this->iris->createPayout(
            beneficiary: [
                'name'           => $withdrawal->bank_account_name,
                'account_number' => $withdrawal->bank_account_number,
                'bank'           => strtolower($withdrawal->bank_name),
                'email'          => $withdrawal->tukang->email ?? '',
                'amount'         => $withdrawal->amount,
                'notes'          => 'Manual approval by admin',
            ],
            referenceNo: $referenceNo,
        );

        if (!$result['success']) {
            Log::error('Admin approve: Iris createPayout failed', [
                'withdrawal_id' => $withdrawal->id,
                'result'        => $result,
            ]);
            return back()->with(
                'error',
                'Gagal kirim ke Iris: ' . ($result['message'] ?? 'Unknown error')
            );
        }

        $withdrawal->update([
            'status'            => 'processing',
            'reference_id'      => $referenceNo,
            'iris_reference_no' => $result['reference_no'] ?? $referenceNo,
            'iris_status'       => $result['status'] ?? 'queued',
            'iris_response'     => $result['data'] ?? [],
        ]);

        return redirect()
            ->route('admin.withdrawals.index')
            ->with('success', 'Withdrawal berhasil dikirim ke Midtrans Iris untuk diproses.');
    }

    // =========================================================================
    // REJECT — Tolak withdrawal (refund saldo ke tukang)
    // POST /admin/withdrawals/{withdrawal}/reject
    // =========================================================================
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        if (!in_array($withdrawal->status, ['pending', 'processing'])) {
            return back()->with('error', 'Withdrawal ini tidak dapat ditolak.');
        }

        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $withdrawal->update([
            'status'       => 'failed',
            'notes'        => $request->notes,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal berhasil ditolak.');
    }

    // =========================================================================
    // SYNC — Manual sync status dari Iris
    // POST /admin/withdrawals/{withdrawal}/sync
    // =========================================================================
    public function syncStatus(Withdrawal $withdrawal)
    {
        if (!$withdrawal->iris_reference_no) {
            return back()->with('error', 'Tidak ada Iris Reference No untuk di-sync.');
        }

        $result = $this->iris->getPayoutDetail($withdrawal->iris_reference_no);

        if (!$result['success']) {
            return back()->with('error', 'Gagal mengambil status dari Iris.');
        }

        $irisStatus = $result['data']['status'] ?? null;

        if (!$irisStatus) {
            return back()->with('error', 'Status Iris tidak ditemukan.');
        }

        $newStatus = match ($irisStatus) {
            'processed' => 'success',
            'failed'    => 'failed',
            default     => 'processing',
        };

        DB::beginTransaction();
        try {
            $withdrawal->update([
                'iris_status'  => $irisStatus,
                'status'       => $newStatus,
                'processed_at' => in_array($irisStatus, ['processed', 'failed'])
                    ? now()
                    : $withdrawal->processed_at,
            ]);

            if ($newStatus === 'success') {
                PartnerEarning::where('tukang_id', $withdrawal->tukang_id)
                    ->where('status', 'settled')
                    ->update(['status' => 'paid']);
            }

            DB::commit();
            return back()->with(
                'success',
                "Status berhasil disync: {$withdrawal->status_label} (Iris: {$irisStatus})"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update status: ' . $e->getMessage());
        }
    }
}


// namespace App\Http\Controllers\Web\Admin;

// use App\Helpers\NotificationHelper;
// use App\Http\Controllers\Controller;
// use App\Models\PartnerEarning;
// use App\Models\Withdrawal;
// use Illuminate\Http\Request;

// class WithdrawalController extends Controller
// {
//     public function index(Request $request)
//     {
//         $query = Withdrawal::with('tukang:id,name,phone');

//         if ($request->filled('status')) {
//             $query->where('status', $request->status);
//         }

//         $withdrawals = $query->latest()->paginate(15)->appends(request()->query());

//         $pendingAmount = Withdrawal::where('status', 'pending')->sum('amount');

//         return view('admin.withdrawals.index', compact('withdrawals', 'pendingAmount'));
//     }

//     public function show(Withdrawal $withdrawal)
//     {
//         $withdrawal->load('tukang:id,name,phone,email');
//         return view('admin.withdrawals.show', compact('withdrawal'));
//     }

//     public function approve(Request $request, Withdrawal $withdrawal)
//     {
//         if ($withdrawal->status !== 'pending') {
//             return back()->with('error', 'Hanya withdrawal pending yang bisa disetujui.');
//         }

//         $request->validate([
//             'reference_id' => 'nullable|string|max:100',
//         ]);

//         $withdrawal->update([
//             'status'       => 'success',
//             'reference_id' => $request->input('reference_id'),
//             'processed_at' => now(),
//         ]);

//         // Update earning tukang → paid
//         PartnerEarning::where('tukang_id', $withdrawal->tukang_id)
//             ->where('status', 'settled')
//             ->update(['status' => 'paid']);

//         NotificationHelper::withdrawalProcessed($withdrawal, 'success');

//         return redirect()
//             ->route('admin.withdrawals.index')
//             ->with('success', 'Penarikan berhasil disetujui dan ditransfer.');
//     }

//     public function reject(Request $request, Withdrawal $withdrawal)
//     {
//         if ($withdrawal->status !== 'pending') {
//             return back()->with('error', 'Hanya withdrawal pending yang bisa ditolak.');
//         }

//         $request->validate([
//             'notes' => 'required|string|max:255',
//         ]);

//         $withdrawal->update([
//             'status'       => 'failed',
//             'notes'        => $request->notes,
//             'processed_at' => now(),
//         ]);

//         NotificationHelper::withdrawalProcessed($withdrawal, 'failed');

//         return back()->with('success', 'Penarikan berhasil ditolak.');
//     }
// }
