<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('tukang:id,name,phone');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate(15)->appends(request()->query());

        $pendingAmount = Withdrawal::where('status', 'pending')->sum('amount');

        return view('admin.withdrawals.index', compact('withdrawals', 'pendingAmount'));
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load('tukang:id,name,phone,email');
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Hanya withdrawal pending yang bisa disetujui.');
        }

        $request->validate([
            'reference_id' => 'nullable|string|max:100',
        ]);

        $withdrawal->update([
            'status'       => 'success',
            'reference_id' => $request->input('reference_id'),
            'processed_at' => now(),
        ]);

        // Update earning tukang → paid
        PartnerEarning::where('tukang_id', $withdrawal->tukang_id)
            ->where('status', 'settled')
            ->update(['status' => 'paid']);

        NotificationHelper::withdrawalProcessed($withdrawal, 'success');

        return redirect()
            ->route('admin.withdrawals.index')
            ->with('success', 'Penarikan berhasil disetujui dan ditransfer.');
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Hanya withdrawal pending yang bisa ditolak.');
        }

        $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $withdrawal->update([
            'status'       => 'failed',
            'notes'        => $request->notes,
            'processed_at' => now(),
        ]);

        NotificationHelper::withdrawalProcessed($withdrawal, 'failed');

        return back()->with('success', 'Penarikan berhasil ditolak.');
    }
}
