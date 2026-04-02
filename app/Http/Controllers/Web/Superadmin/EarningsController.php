<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PartnerEarning;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
public function index()
    {
        $earnings = PartnerEarning::with([
            'order',
            'tukang'
        ])->latest()->paginate();

        return view('pages.admin.earnings.index', compact('earnings'));
    }

    public function pay($id)
    {
        PartnerEarning::findOrFail($id)->update([
            'status' => 'paid'
        ]);

        return back()->with('success', 'Payment success');
    }
}
