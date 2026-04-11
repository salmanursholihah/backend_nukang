<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = SurveyRequest::with([
            'customer:id,name',
            'tukang:id,name',
            'service:id,name',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas(
                'customer',
                fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            );
        }

        $surveys = $query->latest()->paginate(15)->appends(request()->query());

        return view('admin.surveys.index', compact('surveys'));
    }

    public function show(SurveyRequest $survey)
    {
        $survey->load([
            'customer:id,name,phone,email',
            'tukang:id,name,phone',
            'service:id,name,description',
            'surveyServices.service:id,name,unit',
            'order:id,order_number,status',
        ]);

        return view('admin.surveys.show', compact('survey'));
    }
}
