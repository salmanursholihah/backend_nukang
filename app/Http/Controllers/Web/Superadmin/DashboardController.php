<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\SurveyRequest;
use App\Models\Review;
use App\Models\Complaint;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'customers' => User::where('role', 'customer')->count(),
            'tukangs' => User::where('role', 'tukang')->count(),
            'orders' => Order::count(),
            'surveys' => SurveyRequest::count(),
        ];

        $totalReviews = class_exists(Review::class) ? Review::count() : 0;
        $totalSurveys = SurveyRequest::count();
        // $totalComplaints = class_exists(Complaint::class) ? Complaint::count() : 0;

        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.admin.dashboard', compact(
            'data',
            'totalReviews',
            'totalSurveys',
            // 'totalComplaints',
            'recentOrders'
        ));
    }
}
