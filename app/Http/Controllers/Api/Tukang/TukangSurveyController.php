<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;

class TukangSurveyController extends Controller
{
 public function index(Request $request)
    {
        $surveys = SurveyRequest::with(['customer', 'service'])
            ->where('tukang_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $surveys,
        ]);
    }

    public function show(Request $request, $id)
    {
        $survey = SurveyRequest::with(['customer', 'service'])
            ->where('tukang_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $survey,
        ]);
    }

    public function accept(Request $request, $id)
    {
        $survey = SurveyRequest::where('tukang_id', $request->user()->id)
            ->where('status', 'requested')
            ->findOrFail($id);

        $survey->update([
            'status' => 'accepted',
        ]);

        return response()->json([
            'message' => 'Permintaan survei diterima',
            'data' => $survey,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $survey = SurveyRequest::where('tukang_id', $request->user()->id)
            ->whereIn('status', ['requested', 'accepted', 'survey_priced', 'estimated'])
            ->findOrFail($id);

        $survey->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Permintaan survei ditolak',
            'data' => $survey,
        ]);
    }

    public function setSurveyFee(Request $request, $id)
    {
        $data = $request->validate([
            'survey_fee' => ['required', 'numeric', 'min:0'],
            'survey_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $survey = SurveyRequest::where('tukang_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'requested'])
            ->findOrFail($id);

        $survey->update([
            'survey_fee' => $data['survey_fee'],
            'survey_date' => $data['survey_date'] ?? $survey->survey_date,
            'notes' => $data['notes'] ?? $survey->notes,
            'status' => 'survey_priced',
        ]);

        return response()->json([
            'message' => 'Tarif survei berhasil ditentukan',
            'data' => $survey,
        ]);
    }

    public function sendEstimation(Request $request, $id)
    {
        $data = $request->validate([
            'estimated_price' => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $survey = SurveyRequest::where('tukang_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'survey_priced'])
            ->findOrFail($id);

        $survey->update([
            'estimated_price' => $data['estimated_price'],
            'estimated_days' => $data['estimated_days'],
            'notes' => $data['notes'] ?? $survey->notes,
            'status' => 'estimated',
        ]);

        return response()->json([
            'message' => 'Estimasi biaya berhasil dikirim',
            'data' => $survey,
        ]);
    }}
