<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerEarning::with(['tukang:id,name', 'order:id,order_number']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tukang_id')) {
            $query->where('tukang_id', $request->tukang_id);
        }

        $earnings = $query->latest()->paginate(15)->appends(request()->query());

        // Summary total
        $summary = PartnerEarning::selectRaw('
            SUM(CASE WHEN status = "pending"  THEN amount ELSE 0 END) as total_pending,
            SUM(CASE WHEN status = "settled"  THEN amount ELSE 0 END) as total_settled,
            SUM(CASE WHEN status = "paid"     THEN amount ELSE 0 END) as total_paid,
            SUM(platform_fee) as total_platform_fee
        ')->first();

        return view('admin.earnings.index', compact('earnings', 'summary'));
    }

    public function show(PartnerEarning $earning)
    {
        $earning->load(['tukang:id,name,phone', 'order:id,order_number,total_price,completed_at']);
        return view('admin.earnings.show', compact('earning'));
    }

    public function settle(PartnerEarning $earning)
    {
        if ($earning->status !== 'pending') {
            return back()->with('error', 'Hanya earning pending yang bisa di-settle.');
        }

        $earning->update(['status' => 'settled', 'settled_at' => now()]);

        NotificationHelper::earningSettled($earning, $earning->amount);

        return back()->with('success', 'Earning berhasil di-settle.');
    }
}
