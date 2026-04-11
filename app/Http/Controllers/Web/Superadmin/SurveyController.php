<?php

namespace App\Http\Controllers\Web\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = SurveyRequest::with([
            'customer',
            'tukang',
            'service'
        ])->latest()->paginate(10);

        return view('pages.admin.surveys.index', compact('surveys'));
    }

    public function edit($id)
    {
        $survey = SurveyRequest::findOrFail($id);

        $customers = User::where('role', 'customer')->get();
        $tukangs = User::where('role', 'tukang')->get();
        $services = Service::all();

        return view('pages.admin.surveys.edit', compact(
            'survey',
            'customers',
            'tukangs',
            'services'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required',
            'tukang_id' => 'required',
            'service_id' => 'required',
            'address' => 'required',
            'survey_date' => 'nullable',
            'survey_fee' => 'nullable|numeric',
            'estimated_price' => 'nullable|numeric',
            'estimated_days' => 'nullable|integer',
            'notes' => 'nullable',
            'status' => 'required'
        ]);

        $survey = SurveyRequest::findOrFail($id);

        $survey->update([
            'customer_id' => $request->customer_id,
            'tukang_id' => $request->tukang_id,
            'service_id' => $request->service_id,
            'address' => $request->address,
            'survey_date' => $request->survey_date,
            'survey_fee' => $request->survey_fee,
            'estimated_price' => $request->estimated_price,
            'estimated_days' => $request->estimated_days,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('surveys.index')
            ->with('success', 'Survey updated successfully');
    }

    public function destroy($id)
    {
        SurveyRequest::findOrFail($id)->delete();

        return redirect()->route('surveys.index')
            ->with('success', 'Survey deleted successfully');
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->get();

        $tukangs = User::where('role', 'tukang')->get();

        $services = Service::all();

        return view('pages.admin.surveys.create', compact(
            'customers',
            'tukangs',
            'services'
        ));
    }
}
