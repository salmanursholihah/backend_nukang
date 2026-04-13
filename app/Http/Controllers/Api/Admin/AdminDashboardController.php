<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SurveyRequest;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    // =========================================================
    // INDEX — Ringkasan statistik untuk dashboard admin
    // GET /api/admin/dashboard
    // =========================================================

    public function index(): JsonResponse
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
        $revenueStats = Order::where('status', 'completed')
            ->selectRaw('
                SUM(total_price) as total_revenue,
                SUM(service_fee) as total_platform_fee,
                COUNT(*) as total_completed
            ')->first();

        $revenueToday = Order::where('status', 'completed')
            ->whereDate('completed_at', today())
            ->sum('service_fee');

        $revenueThisMonth = Order::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('service_fee');

        // ── Withdrawal Stats ──────────────────────────────────
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $pendingAmount      = Withdrawal::where('status', 'pending')->sum('amount');

        // ── Survey Stats ──────────────────────────────────────
        $pendingSurveys = SurveyRequest::where('status', 'requested')->count();

        // ── Recent Orders ─────────────────────────────────────
        $recentOrders = Order::with([
            'customer:id,name',
            'tukang:id,name',
        ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'status'       => $o->status,
                'total_price'  => $o->total_price,
                'customer'     => $o->customer?->name,
                'tukang'       => $o->tukang?->name,
                'created_at'   => $o->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'users' => [
                    'total_customers'  => $totalCustomers,
                    'total_tukangs'    => $totalTukangs,
                    'new_today'        => $newUsersToday,
                ],
                'orders' => [
                    'total'       => $orderStats->total,
                    'pending'     => $orderStats->pending,
                    'accepted'    => $orderStats->accepted,
                    'on_progress' => $orderStats->on_progress,
                    'completed'   => $orderStats->completed,
                    'cancelled'   => $orderStats->cancelled,
                ],
                'revenue' => [
                    'total_revenue'      => $revenueStats->total_revenue      ?? 0,
                    'total_platform_fee' => $revenueStats->total_platform_fee ?? 0,
                    'today'              => $revenueToday,
                    'this_month'         => $revenueThisMonth,
                ],
                'withdrawals' => [
                    'pending_count'  => $pendingWithdrawals,
                    'pending_amount' => $pendingAmount,
                ],
                'surveys' => [
                    'pending_count' => $pendingSurveys,
                ],
                'recent_orders' => $recentOrders,
            ],
        ]);
    }
}
