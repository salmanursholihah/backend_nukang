<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SurveyRequest;
use App\Models\User;
use App\Models\Withdrawal;

class DashboardController extends Controller
{
    // =========================================================
    // INDEX
    // GET /admin/dashboard
    // =========================================================

    public function index()
    {
        // ── User Stats ────────────────────────────────────────
        $totalCustomers = User::where('role', 'customer')->count();
        $totalTukangs   = User::where('role', 'tukang')->count();
        $newUsersToday  = User::whereDate('created_at', today())->count();

        // ── Order Stats ───────────────────────────────────────
        $orderStats = Order::selectRaw('
            count(*) as total,
            SUM(CASE WHEN status = "pending"     THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "accepted"    THEN 1 ELSE 0 END) as accepted,
            SUM(CASE WHEN status = "on_progress" THEN 1 ELSE 0 END) as on_progress,
            SUM(CASE WHEN status = "completed"   THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = "cancelled"   THEN 1 ELSE 0 END) as cancelled
        ')->first();

        // ── Revenue Stats ─────────────────────────────────────
        $totalRevenue     = Order::where('status', 'completed')->sum('service_fee');
        $revenueToday     = Order::where('status', 'completed')->whereDate('completed_at', today())->sum('service_fee');
        $revenueThisMonth = Order::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('service_fee');

        // ── Pending Actions ───────────────────────────────────
        $pendingWithdrawals    = Withdrawal::where('status', 'pending')->count();
        $pendingWithdrawAmount = Withdrawal::where('status', 'pending')->sum('amount');
        $pendingSurveys        = SurveyRequest::where('status', 'requested')->count();
        $unverifiedTukangs     = User::where('role', 'tukang')
            ->whereHas('tukangProfile', fn($q) => $q->where('is_verified', false))
            ->count();

        // ── Chart Data — Order per hari (7 hari terakhir) ─────
        $chartOrders = Order::selectRaw('DATE(created_at) as date, count(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // ── Chart Data — Revenue per hari (7 hari terakhir) ───
        $chartRevenue = Order::where('status', 'completed')
            ->selectRaw('DATE(completed_at) as date, SUM(service_fee) as total')
            ->where('completed_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // ── Recent Orders (5 terbaru) ─────────────────────────
        $recentOrders = Order::with(['customer:id,name', 'tukang:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        // ── Recent Withdrawals (5 terbaru pending) ─────────────
        $recentWithdrawals = Withdrawal::with('tukang:id,name')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalTukangs',
            'newUsersToday',
            'orderStats',
            'totalRevenue',
            'revenueToday',
            'revenueThisMonth',
            'pendingWithdrawals',
            'pendingWithdrawAmount',
            'pendingSurveys',
            'unverifiedTukangs',
            'chartOrders',
            'chartRevenue',
            'recentOrders',
            'recentWithdrawals'
        ));
    }
}
