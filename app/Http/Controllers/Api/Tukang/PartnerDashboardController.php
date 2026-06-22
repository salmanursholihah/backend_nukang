<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\SurveyRequest;
use App\Models\PartnerEarning;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;

class PartnerDashboardController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $partner = $request->user();
        $partnerId = $partner->id;

        // ── Statistik Order ──────────────────────────────────────────────
        $orderStats = Order::where('tukang_id', $partnerId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending'    THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed'  THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled'  THEN 1 ELSE 0 END) as cancelled
            ")
            ->first();

        // ── Statistik Survey ─────────────────────────────────────────────
        $surveyStats = SurveyRequest::where('tukang_id', $partnerId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        // ── Statistik Earning ────────────────────────────────────────────
        $earningStats = PartnerEarning::where('tukang_id', $partnerId)
            ->selectRaw("
                COALESCE(SUM(amount), 0)                                              as total_earned,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_balance,
                COALESCE(SUM(CASE WHEN status = 'settled' THEN amount ELSE 0 END), 0) as settled_balance
            ")
            ->first();

        // ── Total Withdrawal yang sudah approved ─────────────────────────
        $totalWithdrawn = Withdrawal::where('tukang_id', $partnerId)
            ->where('status', 'approved')
            ->sum('amount');

        // Saldo yang bisa ditarik = settled - sudah withdrawn
        $availableBalance = max(0, $earningStats->settled_balance - $totalWithdrawn);

        // ── Order terbaru (5 terakhir) ───────────────────────────────────
        // $recentOrders = Order::where('tukang_id', $partnerId)
        //     ->with(['customer:id,name,avatar', 'service:id,name'])
        //     ->latest()
        //     ->limit(5)
        //     ->get()
        //     ->map(fn($o) => [
        //         'id'           => $o->id,
        //         'status'       => $o->status,
        //         'total_price'  => $o->total_price,
        //         'scheduled_at' => $o->scheduled_at,
        //         'created_at'   => $o->created_at->toDateTimeString(),
        //         'customer'     => [j
        //             'name'       => $o->customer?->name,
        //             'avatar_url' => $o->customer?->avatar ? asset($o->customer->avatar) : null,
        //         ],
        //         'service_name' => $o->service?->name,
        //     ]);
        $recentOrders = Order::where('tukang_id', $partnerId)
            ->with(['customer:id,name,avatar', 'details:id,order_id,service_name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'status'       => $o->status,
                'total_price'  => $o->total_price,
                'scheduled_at' => $o->scheduled_at,
                'created_at'   => $o->created_at->toDateTimeString(),
                'customer'     => [
                    'name'       => $o->customer?->name,
                    'avatar_url' => $o->customer?->avatar ? asset($o->customer->avatar) : null,
                ],
                'service_name' => $o->details->first()?->service_name,
            ]);

        // ── Survey yang menunggu aksi ────────────────────────────────────
        $pendingSurveys = SurveyRequest::where('tukang_id', $partnerId)
            ->whereIn('status', ['assigned', 'accepted'])
            ->with(['customer:id,name,avatar'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'status'     => $s->status,
                'address'    => $s->address,
                'created_at' => $s->created_at->toDateTimeString(),
                'customer'   => [
                    'name'       => $s->customer?->name,
                    'avatar_url' => $s->customer?->avatar ? asset($s->customer->avatar) : null,
                ],
            ]);

        // ── Profile & Status Online ──────────────────────────────────────
        $partner->load(['tukangProfile', 'tukangLocation']);
        $profile  = $partner->tukangProfile;
        $location = $partner->tukangLocation;

        return response()->json([
            'status' => true,
            'data'   => [

                // Info identitas
                'partner' => [
                    'id'          => $partner->id,
                    'name'        => $partner->name,
                    'avatar_url'  => $partner->avatar ? asset($partner->avatar) : null,
                    'is_verified' => (bool) ($profile?->is_verified ?? false),
                    'is_online'   => (bool) ($location?->is_online ?? false),
                    'rating'      => $profile?->rating ?? 0,
                    'total_reviews' => $profile?->total_reviews ?? 0,
                ],

                // Statistik order
                'order_stats' => [
                    'total'       => (int) $orderStats->total,
                    'pending'     => (int) $orderStats->pending,
                    'in_progress' => (int) $orderStats->in_progress,
                    'completed'   => (int) $orderStats->completed,
                    'cancelled'   => (int) $orderStats->cancelled,
                ],

                // Statistik survey
                'survey_stats' => [
                    'total'     => (int) $surveyStats->total,
                    'pending'   => (int) $surveyStats->pending,
                    'completed' => (int) $surveyStats->completed,
                ],

                // Finansial
                'earning_stats' => [
                    'total_earned'     => (float) $earningStats->total_earned,
                    'pending_balance'  => (float) $earningStats->pending_balance,
                    'settled_balance'  => (float) $earningStats->settled_balance,
                    'total_withdrawn'  => (float) $totalWithdrawn,
                    'available_balance' => (float) $availableBalance,
                ],

                // Data terbaru
                'recent_orders'   => $recentOrders,
                'pending_surveys' => $pendingSurveys,
            ],
        ]);
    }
}
