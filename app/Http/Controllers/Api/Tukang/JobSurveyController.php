<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\SurveyRequest;
use App\Models\SurveyRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobSurveyController extends Controller
{
       // =========================================================
    // INDEX — Daftar survey yang ditugaskan ke tukang
    // GET /api/tukang/surveys
    // Query params:
    //   ?status=requested|accepted|rejected|on_survey|survey_priced|approved|cancelled
    //   ?per_page=10
    // =========================================================
 
    public function index(Request $request): JsonResponse
    {
        $query = SurveyRequest::with([
                'customer:id,name,avatar,phone',
                'service:id,name,thumbnail',
            ])
            ->where('tukang_id', $request->user()->id);
 
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
 
        $surveys = $query->latest()->paginate($request->get('per_page', 10));
 
        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $surveys->total(),
                'current_page' => $surveys->currentPage(),
                'last_page'    => $surveys->lastPage(),
                'summary'      => $this->getStatusSummary($request->user()->id),
            ],
            'data' => collect($surveys->items())->map(fn($s) => $this->formatSurvey($s)),
        ]);
    }
 
 
    // =========================================================
    // SHOW — Detail survey
    // GET /api/tukang/surveys/{survey}
    // =========================================================
 
    public function show(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }
 
        $survey->load([
            'customer:id,name,avatar,phone',
            'service:id,name,thumbnail,description,unit',
            'surveyServices.service:id,name,unit',
            'order:id,order_number,status',
        ]);
 
        return response()->json([
            'status' => true,
            'data'   => $this->formatSurveyDetail($survey),
        ]);
    }
 
 
    // =========================================================
    // ACCEPT — Terima survey
    // PUT /api/tukang/surveys/{survey}/accept
    // =========================================================
 
    public function accept(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }
 
        if ($survey->status !== 'requested') {
            return response()->json([
                'status'  => false,
                'message' => 'Survey hanya bisa diterima jika berstatus requested.',
            ], 422);
        }
 
        $survey->update(['status' => 'accepted']);
 
        return response()->json([
            'status'  => true,
            'message' => 'Survey berhasil diterima. Datang sesuai jadwal yang disepakati.',
            'data'    => $this->formatSurvey($survey->fresh()),
        ]);
    }
 
 
    // =========================================================
    // REJECT — Tolak survey
    // PUT /api/tukang/surveys/{survey}/reject
    // Body:
    //   tukang_notes : string (required)
    // =========================================================
 
    public function reject(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }
 
        if ($survey->status !== 'requested') {
            return response()->json([
                'status'  => false,
                'message' => 'Survey hanya bisa ditolak jika berstatus requested.',
            ], 422);
        }
 
        $request->validate([
            'tukang_notes' => 'required|string|max:255',
        ]);
 
        $survey->update([
            'status'       => 'rejected',
            'tukang_notes' => $request->tukang_notes,
        ]);
 
        return response()->json([
            'status'  => true,
            'message' => 'Survey ditolak.',
            'data'    => $this->formatSurvey($survey->fresh()),
        ]);
    }
 
 
    // =========================================================
    // SET PRICE — Tukang isi estimasi harga setelah survey
    // PUT /api/tukang/surveys/{survey}/set-price
    // Body:
    //   survey_fee      : decimal (optional) biaya survey
    //   estimated_days  : int (required) estimasi hari pengerjaan
    //   tukang_notes    : string (optional) catatan tukang
    //   services        : array (required) detail estimasi per service
    //     - service_id        : int
    //     - estimated_price   : decimal
    //     - qty               : int
    //     - notes             : string (optional)
    // =========================================================
 
    public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }
 
        if (! in_array($survey->status, ['accepted', 'on_survey'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Estimasi hanya bisa diisi setelah survey diterima.',
            ], 422);
        }
 
        $request->validate([
            'survey_fee'                    => 'nullable|numeric|min:0',
            'estimated_days'                => 'required|integer|min:1',
            'tukang_notes'                  => 'nullable|string',
            'services'                      => 'required|array|min:1',
            'services.*.service_id'         => 'required|exists:services,id',
            'services.*.estimated_price'    => 'required|numeric|min:0',
            'services.*.qty'                => 'required|integer|min:1',
            'services.*.notes'              => 'nullable|string',
        ]);
 
        DB::beginTransaction();
        try {
            // Hapus estimasi lama jika ada
            $survey->surveyServices()->delete();
 
            // Hitung total estimasi
            $totalEstimated = 0;
            foreach ($request->services as $item) {
                $service        = Service::find($item['service_id']);
                $itemTotal      = $item['estimated_price'] * $item['qty'];
                $totalEstimated += $itemTotal;
 
                SurveyRequestService::create([
                    'survey_request_id' => $survey->id,
                    'service_id'        => $item['service_id'],
                    'service_name'      => $service->name,
                    'estimated_price'   => $item['estimated_price'],
                    'qty'               => $item['qty'],
                    'notes'             => $item['notes'] ?? null,
                ]);
            }
 
            // Update survey dengan total estimasi
            $survey->update([
                'status'          => 'survey_priced',
                'survey_fee'      => $request->input('survey_fee'),
                'estimated_price' => $totalEstimated,
                'estimated_days'  => $request->estimated_days,
                'tukang_notes'    => $request->input('tukang_notes'),
            ]);
 
            DB::commit();
 
            $survey->load([
                'customer:id,name,avatar',
                'service:id,name,thumbnail',
                'surveyServices.service:id,name,unit',
            ]);
 
            return response()->json([
                'status'  => true,
                'message' => 'Estimasi harga berhasil dikirim. Menunggu persetujuan customer.',
                'data'    => $this->formatSurveyDetail($survey),
            ]);
 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan estimasi. Silakan coba lagi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
 
 
    // =========================================================
    // PRIVATE HELPERS
    // =========================================================
 
    private function getStatusSummary(int $tukangId): array
    {
        $counts = SurveyRequest::where('tukang_id', $tukangId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
 
        return [
            'requested'    => $counts['requested']    ?? 0,
            'accepted'     => $counts['accepted']     ?? 0,
            'on_survey'    => $counts['on_survey']    ?? 0,
            'survey_priced'=> $counts['survey_priced']?? 0,
            'approved'     => $counts['approved']     ?? 0,
            'rejected'     => $counts['rejected']     ?? 0,
            'cancelled'    => $counts['cancelled']    ?? 0,
        ];
    }
 
    private function formatSurvey(SurveyRequest $survey): array
    {
        return [
            'id'              => $survey->id,
            'status'          => $survey->status,
            'address'         => $survey->address,
            'latitude'        => $survey->latitude,
            'longitude'       => $survey->longitude,
            'survey_date'     => $survey->survey_date?->toDateTimeString(),
            'survey_fee'      => $survey->survey_fee,
            'estimated_price' => $survey->estimated_price,
            'estimated_days'  => $survey->estimated_days,
            'notes'           => $survey->notes,
            'tukang_notes'    => $survey->tukang_notes,
            'created_at'      => $survey->created_at->toDateTimeString(),
            'service'         => $survey->relationLoaded('service') ? [
                'id'            => $survey->service->id,
                'name'          => $survey->service->name,
                'thumbnail_url' => $survey->service->thumbnail
                    ? asset($survey->service->thumbnail) : null,
            ] : null,
            'customer'        => $survey->relationLoaded('customer') ? [
                'id'         => $survey->customer->id,
                'name'       => $survey->customer->name,
                'phone'      => $survey->customer->phone,
                'avatar_url' => $survey->customer->avatar
                    ? asset($survey->customer->avatar) : null,
            ] : null,
        ];
    }
 
    private function formatSurveyDetail(SurveyRequest $survey): array
    {
        $data = $this->formatSurvey($survey);
 
        $data['service']['description'] = $survey->service?->description;
        $data['service']['unit']        = $survey->service?->unit;
 
        // Detail estimasi per service
        $data['survey_services'] = $survey->relationLoaded('surveyServices')
            ? $survey->surveyServices->map(fn($ss) => [
                'id'              => $ss->id,
                'service_id'      => $ss->service_id,
                'service_name'    => $ss->service_name,
                'unit'            => $ss->service?->unit,
                'estimated_price' => $ss->estimated_price,
                'qty'             => $ss->qty,
                'subtotal'        => ($ss->estimated_price ?? 0) * $ss->qty,
                'notes'           => $ss->notes,
            ]) : [];
 
        // Link ke order jika sudah approved
        $data['order'] = $survey->relationLoaded('order') && $survey->order ? [
            'id'           => $survey->order->id,
            'order_number' => $survey->order->order_number,
            'status'       => $survey->order->status,
        ] : null;
 
        return $data;
    }
}
