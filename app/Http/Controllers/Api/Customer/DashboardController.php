<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'survey_requested' => SurveyRequest::where('customer_id', $user->id)->count(),
                'active_orders' => Order::where('customer_id', $user->id)
                    ->whereIn('status', ['accepted', 'on_progress'])
                    ->count(),
                'completed_orders' => Order::where('customer_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
            ],
        ]);
    }
}
