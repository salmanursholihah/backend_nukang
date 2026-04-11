<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SurveyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyRequestController extends Controller
{
    // =========================================================
    // INDEX — Daftar survey milik customer
    // GET /api/customer/survey-requests
    // Query params:
    //   ?status=requested|accepted|rejected|on_survey|survey_priced|approved|cancelled
    //   ?per_page=10
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = SurveyRequest::with([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating,is_verified',
            'service:id,name,thumbnail',
            'surveyServices.service:id,name',
        ])
            ->where('customer_id', $request->user()->id);

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
            ],
            'data' => collect($surveys->items())->map(fn($s) => $this->formatSurvey($s)),
        ]);
    }


    // =========================================================
    // STORE — Ajukan survey baru
    // POST /api/customer/survey-requests
    // Body:
    //   tukang_id     : int (required)
    //   service_id    : int (required)
    //   address       : string (required)
    //   latitude      : decimal (required)
    //   longitude     : decimal (required)
    //   survey_date   : datetime (required)
    //   notes         : string (optional)
    // =========================================================

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tukang_id'   => 'required|exists:users,id',
            'service_id'  => 'required|exists:services,id',
            'address'     => 'required|string',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'survey_date' => 'required|date|after:now',
            'notes'       => 'nullable|string',
        ]);

        // Cek apakah tukang bisa mengerjakan service ini
        $canDo = DB::table('tukang_services')
            ->where('tukang_id', $request->tukang_id)
            ->where('service_id', $request->service_id)
            ->exists();

        if (! $canDo) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang ini tidak melayani service yang dipilih.',
            ], 422);
        }

        // Cek apakah sudah ada survey yang sedang berjalan untuk tukang + service yang sama
        $exists = SurveyRequest::where('customer_id', $request->user()->id)
            ->where('tukang_id', $request->tukang_id)
            ->where('service_id', $request->service_id)
            ->whereNotIn('status', ['rejected', 'cancelled', 'approved'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Kamu sudah memiliki survey aktif untuk tukang dan service ini.',
            ], 422);
        }

        $survey = SurveyRequest::create([
            'customer_id' => $request->user()->id,
            'tukang_id'   => $request->tukang_id,
            'service_id'  => $request->service_id,
            'address'     => $request->address,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'survey_date' => $request->survey_date,
            'notes'       => $request->notes,
            'status'      => 'requested',
        ]);

        $survey->load([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating',
            'service:id,name,thumbnail',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Permintaan survey berhasil dikirim. Menunggu konfirmasi tukang.',
            'data'    => $this->formatSurvey($survey),
        ], 201);
    }


    // =========================================================
    // SHOW — Detail survey
    // GET /api/customer/survey-requests/{survey}
    // =========================================================

    public function show(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }

        $survey->load([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating,is_verified,city',
            'tukang.tukangLocation:tukang_id,latitude,longitude,is_online',
            'service:id,name,thumbnail,description',
            'surveyServices.service:id,name,unit',
            'order:id,order_number,status',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $this->formatSurveyDetail($survey),
        ]);
    }


    // =========================================================
    // APPROVE — Customer setuju estimasi → otomatis jadi Order
    // PUT /api/customer/survey-requests/{survey}/approve
    // =========================================================

    public function approve(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }

        // Hanya bisa approve jika status survey_priced
        if ($survey->status !== 'survey_priced') {
            return response()->json([
                'status'  => false,
                'message' => 'Survey belum memiliki estimasi harga dari tukang.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update status survey → approved
            $survey->update(['status' => 'approved']);

            // Ambil detail service dari survey
            $surveyServices = $survey->surveyServices()->with('service')->get();

            // Hitung subtotal dari survey services
            $subtotal = $surveyServices->sum(fn($ss) => ($ss->estimated_price ?? 0) * $ss->qty);
            $serviceFee = $subtotal * 0.10;
            $totalPrice = $subtotal + $serviceFee;

            // Buat Order dari survey ini
            $order = Order::create([
                'customer_id'       => $survey->customer_id,
                'tukang_id'         => $survey->tukang_id,
                'survey_request_id' => $survey->id,
                'address'           => $survey->address,
                'latitude'          => $survey->latitude,
                'longitude'         => $survey->longitude,
                'service_date'      => $survey->survey_date,
                'subtotal'          => $subtotal,
                'service_fee'       => $serviceFee,
                'total_price'       => $totalPrice,
                'status'            => 'pending',
            ]);

            // Buat order details dari survey services
            foreach ($surveyServices as $ss) {
                OrderDetail::create([
                    'order_id'     => $order->id,
                    'service_id'   => $ss->service_id,
                    'service_name' => $ss->service_name,
                    'price'        => $ss->estimated_price ?? 0,
                    'qty'          => $ss->qty,
                    'subtotal'     => ($ss->estimated_price ?? 0) * $ss->qty,
                ]);
            }

            DB::commit();

            $order->load([
                'tukang:id,name,avatar',
                'details',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Estimasi disetujui. Order berhasil dibuat!',
                'data'    => [
                    'survey' => $this->formatSurvey($survey->fresh()),
                    'order'  => [
                        'id'           => $order->id,
                        'order_number' => $order->order_number,
                        'total_price'  => $order->total_price,
                        'status'       => $order->status,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat order dari survey. Silakan coba lagi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    // =========================================================
    // CANCEL — Batalkan survey
    // DELETE /api/customer/survey-requests/{survey}
    // =========================================================

    public function cancel(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }

        // Hanya bisa cancel jika belum approved
        if (in_array($survey->status, ['approved', 'cancelled'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak bisa dibatalkan.',
            ], 422);
        }

        $survey->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'Survey berhasil dibatalkan.',
            'data'    => $this->formatSurvey($survey->fresh()),
        ]);
    }


    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function formatSurvey(SurveyRequest $survey): array
    {
        return [
            'id'              => $survey->id,
            'status'          => $survey->status,
            'address'         => $survey->address,
            'survey_date'     => $survey->survey_date?->toDateTimeString(),
            'survey_fee'      => $survey->survey_fee,
            'estimated_price' => $survey->estimated_price,
            'estimated_days'  => $survey->estimated_days,
            'notes'           => $survey->notes,
            'created_at'      => $survey->created_at->toDateTimeString(),
            'service'         => $survey->relationLoaded('service') ? [
                'id'            => $survey->service->id,
                'name'          => $survey->service->name,
                'thumbnail_url' => $survey->service->thumbnail
                    ? asset($survey->service->thumbnail) : null,
            ] : null,
            'tukang'          => $survey->relationLoaded('tukang') ? [
                'id'          => $survey->tukang->id,
                'name'        => $survey->tukang->name,
                'avatar_url'  => $survey->tukang->avatar ? asset($survey->tukang->avatar) : null,
                'photo_url'   => $survey->tukang->tukangProfile?->photo
                    ? asset($survey->tukang->tukangProfile->photo) : null,
                'rating'      => $survey->tukang->tukangProfile?->rating,
                'is_verified' => $survey->tukang->tukangProfile?->is_verified,
            ] : null,
        ];
    }

    private function formatSurveyDetail(SurveyRequest $survey): array
    {
        $data = $this->formatSurvey($survey);

        // Tambahan data untuk detail
        $data['tukang_notes']   = $survey->tukang_notes;
        $data['tukang']['city'] = $survey->tukang?->tukangProfile?->city;
        $data['tukang']['location'] = $survey->tukang?->tukangLocation ? [
            'latitude'  => $survey->tukang->tukangLocation->latitude,
            'longitude' => $survey->tukang->tukangLocation->longitude,
            'is_online' => $survey->tukang->tukangLocation->is_online,
        ] : null;

        $data['service']['description'] = $survey->service?->description;

        // Detail estimasi service dari tukang
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
