<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PartnerEarning;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;

class TukangDashboardController extends Controller
{
public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'survey_requests' => SurveyRequest::where('tukang_id', $user->id)->count(),
                'active_orders' => Order::where('tukang_id', $user->id)
                    ->whereIn('status', ['accepted', 'on_progress'])
                    ->count(),
                'completed_orders' => Order::where('tukang_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
                'pending_earnings' => PartnerEarning::where('tukang_id', $user->id)
                    ->where('status', 'pending')
                    ->sum('amount'),
            ],
        ]);
    }}
