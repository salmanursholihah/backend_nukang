<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
 public function index()
    {
        $data = [
            'customers' => User::where('role', 'customer')->count(),
            'tukangs' => User::where('role', 'tukang')->count(),
            'orders' => Order::count(),
            'surveys' => SurveyRequest::count(),
            'earnings_pending' => PartnerEarning::where('status', 'pending')->sum('amount'),
            'earnings_paid' => PartnerEarning::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.dashboard', compact('data'));
    }
}