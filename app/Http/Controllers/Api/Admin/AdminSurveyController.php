<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSurveyController extends Controller
{
    // GET /api/admin/surveys
    public function index(Request $request): JsonResponse
    {
        $query = SurveyRequest::with([
            'customer:id,name,phone',
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

        $surveys = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $surveys->total(),
                'current_page' => $surveys->currentPage(),
                'last_page'    => $surveys->lastPage(),
            ],
            'data' => collect($surveys->items())->map(fn($s) => [
                'id'              => $s->id,
                'status'          => $s->status,
                'address'         => $s->address,
                'survey_date'     => $s->survey_date?->toDateTimeString(),
                'estimated_price' => $s->estimated_price,
                'estimated_days'  => $s->estimated_days,
                'created_at'      => $s->created_at->toDateTimeString(),
                'customer'        => ['id' => $s->customer?->id, 'name' => $s->customer?->name, 'phone' => $s->customer?->phone],
                'tukang'          => ['id' => $s->tukang?->id,   'name' => $s->tukang?->name],
                'service'         => ['id' => $s->service?->id,  'name' => $s->service?->name],
            ]),
        ]);
    }

    // GET /api/admin/surveys/{survey}
    public function show(SurveyRequest $survey): JsonResponse
    {
        $survey->load([
            'customer:id,name,phone,email',
            'tukang:id,name,phone',
            'service:id,name,description',
            'surveyServices.service:id,name,unit',
            'order:id,order_number,status',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $survey,
        ]);
    }
}
