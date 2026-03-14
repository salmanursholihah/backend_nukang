<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Service;
use App\Models\SurveyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
  public function index(Request $request)
    {
        $surveys = SurveyRequest::with(['service', 'tukang.tukangProfile'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $surveys,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tukang_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'address' => ['required', 'string'],
            'survey_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $survey = SurveyRequest::create([
            'customer_id' => $request->user()->id,
            'tukang_id' => $data['tukang_id'],
            'service_id' => $data['service_id'],
            'address' => $data['address'],
            'survey_date' => $data['survey_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'requested',
        ]);

        return response()->json([
            'message' => 'Permintaan survei berhasil dibuat',
            'data' => $survey->load(['service', 'tukang.tukangProfile']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $survey = SurveyRequest::with(['service', 'tukang.tukangProfile'])
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $survey,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $survey = SurveyRequest::where('customer_id', $request->user()->id)
            ->where('status', 'estimated')
            ->findOrFail($id);

        $service = Service::findOrFail($survey->service_id);

        $order = DB::transaction(function () use ($survey, $service) {
            $survey->update([
                'status' => 'approved',
            ]);

            $order = Order::create([
                'customer_id' => $survey->customer_id,
                'tukang_id' => $survey->tukang_id,
                'total_price' => $survey->estimated_price ?? 0,
                'service_date' => $survey->survey_date ?? now(),
                'address' => $survey->address,
                'status' => 'accepted',
            ]);

            OrderDetail::create([
                'order_id' => $order->id,
                'service_id' => $survey->service_id,
                'price' => $survey->estimated_price ?? ($service->price ?? 0),
                'qty' => 1,
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Estimasi disetujui dan order berhasil dibuat',
            'data' => $order->load(['details.service']),
        ]);
    }

    public function reject(Request $request, $id)
    {
        $survey = SurveyRequest::where('customer_id', $request->user()->id)
            ->findOrFail($id);

        $survey->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Estimasi ditolak',
            'data' => $survey,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $survey = SurveyRequest::where('customer_id', $request->user()->id)
            ->whereIn('status', ['requested', 'accepted', 'survey_priced', 'estimated'])
            ->findOrFail($id);

        $survey->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Permintaan survei dibatalkan',
            'data' => $survey,
        ]);
    }}
