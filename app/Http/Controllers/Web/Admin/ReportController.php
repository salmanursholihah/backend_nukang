<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function orders(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $orders = Order::with(['customer:id,name', 'tukang:id,name'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = Order::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('
                count(*) as total,
                SUM(CASE WHEN status = "completed"  THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "cancelled"  THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = "completed"  THEN total_price ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = "completed"  THEN service_fee ELSE 0 END) as total_platform_fee
            ')->first();

        return view('admin.reports.orders', compact('orders', 'summary', 'dateFrom', 'dateTo'));
    }

    public function revenue(Request $request)
    {
        $year = $request->get('year', now()->year);

        // Revenue per bulan
        $monthlyRevenue = Order::where('status', 'completed')
            ->whereYear('completed_at', $year)
            ->selectRaw('MONTH(completed_at) as month, SUM(service_fee) as total, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $totalRevenue = array_sum($monthlyRevenue);

        return view('admin.reports.revenue', compact('monthlyRevenue', 'totalRevenue', 'year'));
    }

    public function tukangs(Request $request)
    {
        $tukangs = User::where('role', 'tukang')
            ->with('tukangProfile:user_id,rating,total_jobs,total_reviews,is_verified,city')
            ->withCount([
                'jobOrders as completed_orders' => fn($q) => $q->where('status', 'completed'),
            ])
            ->withSum(['earnings as total_earned' => fn($q) => $q->where('status', 'paid')], 'amount')
            ->orderByDesc('completed_orders')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.tukangs', compact('tukangs'));
    }
}
