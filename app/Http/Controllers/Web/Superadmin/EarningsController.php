<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PartnerEarning;
use App\Models\User;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function index()
    {
        $earnings = PartnerEarning::with([
            'order',
            'tukang'
        ])->latest()->paginate(10);

        return view('pages.admin.earnings.index', compact('earnings'));
    }

    public function create()
    {
        $orders = Order::all();
        $tukangs = User::where('role', 'tukang')->get();

        return view('pages.admin.earnings.create', compact('orders', 'tukangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tukang_id' => 'required',
            'order_id' => 'required',
            'amount' => 'required|numeric',
            'status' => 'required'
        ]);

        PartnerEarning::create([
            'tukang_id' => $request->tukang_id,
            'order_id' => $request->order_id,
            'amount' => $request->amount,
            'status' => $request->status,
        ]);

        return redirect()->route('earnings.index')
            ->with('success', 'Earning created successfully');
    }

    public function edit($id)
    {
        $earning = PartnerEarning::findOrFail($id);

        return view('pages.admin.earnings.edit', compact('earning'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'status' => 'required'
        ]);

        $earning = PartnerEarning::findOrFail($id);

        $earning->update([
            'amount' => $request->amount,
            'status' => $request->status,
        ]);

        return redirect()->route('earnings.index')
            ->with('success', 'Earning updated successfully');
    }

    public function show($id)
    {
        $earning = PartnerEarning::with([
            'order',
            'tukang'
        ])->findOrFail($id);

        return view('pages.admin.earnings.show', compact('earning'));
    }

    public function destroy($id)
    {
        PartnerEarning::findOrFail($id)->delete();

        return redirect()->route('earnings.index')
            ->with('success', 'Earning deleted successfully');
    }

    public function pay($id)
    {
        PartnerEarning::findOrFail($id)->update([
            'status' => 'paid'
        ]);

        return back()->with('success', 'Payment success');
    }
}
