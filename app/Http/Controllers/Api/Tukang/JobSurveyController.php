<?php

// // namespace App\Http\Controllers\Api\Tukang;

// // use App\Http\Controllers\Controller;
// // use App\Models\Service;
// // use App\Models\SurveyRequest;
// // use App\Models\SurveyRequestService;
// // use Illuminate\Http\JsonResponse;
// // use Illuminate\Http\Request;
// // use Illuminate\Support\Facades\DB;

// // class JobSurveyController extends Controller
// // {
// //        // =========================================================
// //     // INDEX — Daftar survey yang ditugaskan ke tukang
// //     // GET /api/tukang/surveys
// //     // Query params:
// //     //   ?status=requested|accepted|rejected|on_survey|survey_priced|approved|cancelled
// //     //   ?per_page=10
// //     // =========================================================

// //     public function index(Request $request): JsonResponse
// //     {
// //         $query = SurveyRequest::with([
// //                 'customer:id,name,avatar,phone',
// //                 'service:id,name,thumbnail',
// //             ])
// //             ->where('tukang_id', $request->user()->id);

// //         if ($request->filled('status')) {
// //             $query->where('status', $request->status);
// //         }

// //         $surveys = $query->latest()->paginate($request->get('per_page', 10));

// //         return response()->json([
// //             'status' => true,
// //             'meta'   => [
// //                 'total'        => $surveys->total(),
// //                 'current_page' => $surveys->currentPage(),
// //                 'last_page'    => $surveys->lastPage(),
// //                 'summary'      => $this->getStatusSummary($request->user()->id),
// //             ],
// //             'data' => collect($surveys->items())->map(fn($s) => $this->formatSurvey($s)),
// //         ]);
// //     }


// //     // =========================================================
// //     // SHOW — Detail survey
// //     // GET /api/tukang/surveys/{survey}
// //     // =========================================================

// //     public function show(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak ditemukan.',
// //             ], 404);
// //         }

// //         $survey->load([
// //             'customer:id,name,avatar,phone',
// //             'service:id,name,thumbnail,description,unit',
// //             'surveyServices.service:id,name,unit',
// //             'order:id,order_number,status',
// //         ]);

// //         return response()->json([
// //             'status' => true,
// //             'data'   => $this->formatSurveyDetail($survey),
// //         ]);
// //     }


// //     // =========================================================
// //     // ACCEPT — Terima survey
// //     // PUT /api/tukang/surveys/{survey}/accept
// //     // =========================================================

// //     public function accept(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak ditemukan.',
// //             ], 404);
// //         }

// //         if ($survey->status !== 'requested') {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey hanya bisa diterima jika berstatus requested.',
// //             ], 422);
// //         }

// //         $survey->update(['status' => 'accepted']);

// //         return response()->json([
// //             'status'  => true,
// //             'message' => 'Survey berhasil diterima. Datang sesuai jadwal yang disepakati.',
// //             'data'    => $this->formatSurvey($survey->fresh()),
// //         ]);
// //     }


// //     // =========================================================
// //     // REJECT — Tolak survey
// //     // PUT /api/tukang/surveys/{survey}/reject
// //     // Body:
// //     //   tukang_notes : string (required)
// //     // =========================================================

// //     public function reject(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak ditemukan.',
// //             ], 404);
// //         }

// //         if ($survey->status !== 'requested') {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey hanya bisa ditolak jika berstatus requested.',
// //             ], 422);
// //         }

// //         $request->validate([
// //             'tukang_notes' => 'required|string|max:255',
// //         ]);

// //         $survey->update([
// //             'status'       => 'rejected',
// //             'tukang_notes' => $request->tukang_notes,
// //         ]);

// //         return response()->json([
// //             'status'  => true,
// //             'message' => 'Survey ditolak.',
// //             'data'    => $this->formatSurvey($survey->fresh()),
// //         ]);
// //     }


// //     // =========================================================
// //     // SET PRICE — Tukang isi estimasi harga setelah survey
// //     // PUT /api/tukang/surveys/{survey}/set-price
// //     // Body:
// //     //   survey_fee      : decimal (optional) biaya survey
// //     //   estimated_days  : int (required) estimasi hari pengerjaan
// //     //   tukang_notes    : string (optional) catatan tukang
// //     //   services        : array (required) detail estimasi per service
// //     //     - service_id        : int
// //     //     - estimated_price   : decimal
// //     //     - qty               : int
// //     //     - notes             : string (optional)
// //     // =========================================================

// //     public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak ditemukan.',
// //             ], 404);
// //         }

// //         if (! in_array($survey->status, ['accepted', 'on_survey'])) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Estimasi hanya bisa diisi setelah survey diterima.',
// //             ], 422);
// //         }

// //         $request->validate([
// //             'survey_fee'                    => 'nullable|numeric|min:0',
// //             'estimated_days'                => 'required|integer|min:1',
// //             'tukang_notes'                  => 'nullable|string',
// //             'services'                      => 'required|array|min:1',
// //             'services.*.service_id'         => 'required|exists:services,id',
// //             'services.*.estimated_price'    => 'required|numeric|min:0',
// //             'services.*.qty'                => 'required|integer|min:1',
// //             'services.*.notes'              => 'nullable|string',
// //         ]);

// //         DB::beginTransaction();
// //         try {
// //             // Hapus estimasi lama jika ada
// //             $survey->surveyServices()->delete();

// //             // Hitung total estimasi
// //             $totalEstimated = 0;
// //             foreach ($request->services as $item) {
// //                 $service        = Service::find($item['service_id']);
// //                 $itemTotal      = $item['estimated_price'] * $item['qty'];
// //                 $totalEstimated += $itemTotal;

// //                 SurveyRequestService::create([
// //                     'survey_request_id' => $survey->id,
// //                     'service_id'        => $item['service_id'],
// //                     'service_name'      => $service->name,
// //                     'estimated_price'   => $item['estimated_price'],
// //                     'qty'               => $item['qty'],
// //                     'notes'             => $item['notes'] ?? null,
// //                 ]);
// //             }

// //             // Update survey dengan total estimasi
// //             $survey->update([
// //                 'status'          => 'survey_priced',
// //                 'survey_fee'      => $request->input('survey_fee'),
// //                 'estimated_price' => $totalEstimated,
// //                 'estimated_days'  => $request->estimated_days,
// //                 'tukang_notes'    => $request->input('tukang_notes'),
// //             ]);

// //             DB::commit();

// //             $survey->load([
// //                 'customer:id,name,avatar',
// //                 'service:id,name,thumbnail',
// //                 'surveyServices.service:id,name,unit',
// //             ]);

// //             return response()->json([
// //                 'status'  => true,
// //                 'message' => 'Estimasi harga berhasil dikirim. Menunggu persetujuan customer.',
// //                 'data'    => $this->formatSurveyDetail($survey),
// //             ]);

// //         } catch (\Exception $e) {
// //             DB::rollBack();
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Gagal menyimpan estimasi. Silakan coba lagi.',
// //                 'error'   => $e->getMessage(),
// //             ], 500);
// //         }
// //     }


// //     // =========================================================
// //     // PRIVATE HELPERS
// //     // =========================================================

// //     private function getStatusSummary(int $tukangId): array
// //     {
// //         $counts = SurveyRequest::where('tukang_id', $tukangId)
// //             ->selectRaw('status, count(*) as total')
// //             ->groupBy('status')
// //             ->pluck('total', 'status')
// //             ->toArray();

// //         return [
// //             'requested'    => $counts['requested']    ?? 0,
// //             'accepted'     => $counts['accepted']     ?? 0,
// //             'on_survey'    => $counts['on_survey']    ?? 0,
// //             'survey_priced'=> $counts['survey_priced']?? 0,
// //             'approved'     => $counts['approved']     ?? 0,
// //             'rejected'     => $counts['rejected']     ?? 0,
// //             'cancelled'    => $counts['cancelled']    ?? 0,
// //         ];
// //     }

// //     private function formatSurvey(SurveyRequest $survey): array
// //     {
// //         return [
// //             'id'              => $survey->id,
// //             'status'          => $survey->status,
// //             'address'         => $survey->address,
// //             'latitude'        => $survey->latitude,
// //             'longitude'       => $survey->longitude,
// //             'survey_date'     => $survey->survey_date?->toDateTimeString(),
// //             'survey_fee'      => $survey->survey_fee,
// //             'estimated_price' => $survey->estimated_price,
// //             'estimated_days'  => $survey->estimated_days,
// //             'notes'           => $survey->notes,
// //             'tukang_notes'    => $survey->tukang_notes,
// //             'created_at'      => $survey->created_at->toDateTimeString(),
// //             'service'         => $survey->relationLoaded('service') ? [
// //                 'id'            => $survey->service->id,
// //                 'name'          => $survey->service->name,
// //                 'thumbnail_url' => $survey->service->thumbnail
// //                     ? asset($survey->service->thumbnail) : null,
// //             ] : null,
// //             'customer'        => $survey->relationLoaded('customer') ? [
// //                 'id'         => $survey->customer->id,
// //                 'name'       => $survey->customer->name,
// //                 'phone'      => $survey->customer->phone,
// //                 'avatar_url' => $survey->customer->avatar
// //                     ? asset($survey->customer->avatar) : null,
// //             ] : null,
// //         ];
// //     }

// //     private function formatSurveyDetail(SurveyRequest $survey): array
// //     {
// //         $data = $this->formatSurvey($survey);

// //         $data['service']['description'] = $survey->service?->description;
// //         $data['service']['unit']        = $survey->service?->unit;

// //         // Detail estimasi per service
// //         $data['survey_services'] = $survey->relationLoaded('surveyServices')
// //             ? $survey->surveyServices->map(fn($ss) => [
// //                 'id'              => $ss->id,
// //                 'service_id'      => $ss->service_id,
// //                 'service_name'    => $ss->service_name,
// //                 'unit'            => $ss->service?->unit,
// //                 'estimated_price' => $ss->estimated_price,
// //                 'qty'             => $ss->qty,
// //                 'subtotal'        => ($ss->estimated_price ?? 0) * $ss->qty,
// //                 'notes'           => $ss->notes,
// //             ]) : [];

// //         // Link ke order jika sudah approved
// //         $data['order'] = $survey->relationLoaded('order') && $survey->order ? [
// //             'id'           => $survey->order->id,
// //             'order_number' => $survey->order->order_number,
// //             'status'       => $survey->order->status,
// //         ] : null;

// //         return $data;
// //     }
// // }



// // namespace App\Http\Controllers\Api\Tukang;

// // use App\Http\Controllers\Controller;
// // use App\Models\Notification;
// // use App\Models\SurveyRequest;
// // use App\Models\SurveyRequestService;
// // use Illuminate\Http\JsonResponse;
// // use Illuminate\Http\Request;
// // use Illuminate\Support\Facades\DB;

// // // =========================================================
// // // Controller khusus TUKANG untuk mengelola survey request
// // // Route prefix: /api/tukang/survey-requests
// // // =========================================================

// // class JobSurveyController extends Controller
// // {
// //     // ── Daftar survey yang masuk ke tukang ────────────────
// //     // GET /api/tukang/survey-requests
// //     public function index(Request $request): JsonResponse
// //     {
// //         $surveys = SurveyRequest::with([
// //             'customer:id,name,avatar',
// //             'service:id,name,thumbnail',
// //         ])
// //             ->where('tukang_id', $request->user()->id)
// //             ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
// //             ->latest()
// //             ->paginate($request->get('per_page', 10));

// //         return response()->json([
// //             'status' => true,
// //             'meta'   => [
// //                 'total'        => $surveys->total(),
// //                 'current_page' => $surveys->currentPage(),
// //                 'last_page'    => $surveys->lastPage(),
// //             ],
// //             'data' => $surveys->items(),
// //         ]);
// //     }

// //     // ── Terima survey ──────────────────────────────────────
// //     // PUT /api/tukang/survey-requests/{survey}/accept
// //     public function accept(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Tidak ditemukan.'], 404);
// //         }

// //         if ($survey->status !== 'requested') {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak dalam status yang bisa diterima.',
// //             ], 422);
// //         }

// //         $survey->update(['status' => 'accepted']);

// //         // ── Notifikasi ke CUSTOMER ─────────────────────────
// //         Notification::send(
// //             userId: $survey->customer_id,
// //             title: 'Survey Diterima!',
// //             body: "{$request->user()->name} menerima permintaan surveymu. Tunggu jadwal kedatangan.",
// //             type: 'survey',
// //             notifiable: $survey,
// //         );

// //     // ← TAMBAHKAN INI
// //     \App\Services\NotificationService::surveyApprovedToCustomer($survey);


// //         return response()->json([
// //             'status'  => true,
// //             'message' => 'Survey berhasil diterima.',
// //             'data'    => $survey->fresh(),
// //         ]);
// //     }

// //     // ── Tolak survey ───────────────────────────────────────
// //     // PUT /api/tukang/survey-requests/{survey}/reject
// //     public function reject(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Tidak ditemukan.'], 404);
// //         }

// //         if ($survey->status !== 'requested') {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak dalam status yang bisa ditolak.',
// //             ], 422);
// //         }

// //         $survey->update(['status' => 'rejected']);

// //         // ── Notifikasi ke CUSTOMER ─────────────────────────
// //         Notification::send(
// //             userId: $survey->customer_id,
// //             title: 'Survey Ditolak',
// //             body: "Maaf, {$request->user()->name} tidak dapat memenuhi permintaan surveymu saat ini.",
// //             type: 'survey',
// //             notifiable: $survey,
// //         );

// //         return response()->json([
// //             'status'  => true,
// //             'message' => 'Survey berhasil ditolak.',
// //             'data'    => $survey->fresh(),
// //         ]);
// //     }

// //     // ── Tukang isi estimasi harga ──────────────────────────
// //     // PUT /api/tukang/survey-requests/{survey}/price
// //     // Body:
// //     //   survey_fee      : decimal (biaya survey)
// //     //   estimated_days  : int
// //     //   tukang_notes    : string (optional)
// //     //   services        : array of { service_id, service_name, estimated_price, qty, notes? }
// //     public function price(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Tidak ditemukan.'], 404);
// //         }

// //         if ($survey->status !== 'accepted' && $survey->status !== 'on_survey') {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey belum bisa diisi estimasi.',
// //             ], 422);
// //         }

// //         $request->validate([
// //             'survey_fee'          => 'nullable|numeric|min:0',
// //             'estimated_days'      => 'required|integer|min:1',
// //             'tukang_notes'        => 'nullable|string',
// //             'services'            => 'required|array|min:1',
// //             'services.*.service_id'      => 'required|integer|exists:services,id',
// //             'services.*.service_name'    => 'required|string',
// //             'services.*.estimated_price' => 'required|numeric|min:0',
// //             'services.*.qty'             => 'required|integer|min:1',
// //             'services.*.notes'           => 'nullable|string',
// //         ]);

// //         DB::beginTransaction();
// //         try {
// //             // Hitung total estimasi
// //             $totalEstimated = collect($request->services)
// //                 ->sum(fn($s) => $s['estimated_price'] * $s['qty']);

// //             $survey->update([
// //                 'status'          => 'survey_priced',
// //                 'survey_fee'      => $request->survey_fee ?? 0,
// //                 'estimated_price' => $totalEstimated,
// //                 'estimated_days'  => $request->estimated_days,
// //                 'tukang_notes'    => $request->tukang_notes,
// //             ]);

// //             // Hapus detail lama lalu insert baru
// //             $survey->surveyServices()->delete();

// //             foreach ($request->services as $svc) {
// //                 SurveyRequestService::create([
// //                     'survey_request_id' => $survey->id,
// //                     'service_id'        => $svc['service_id'],
// //                     'service_name'      => $svc['service_name'],
// //                     'estimated_price'   => $svc['estimated_price'],
// //                     'qty'               => $svc['qty'],
// //                     'notes'             => $svc['notes'] ?? null,
// //                 ]);
// //             }

// //             // ── Notifikasi ke CUSTOMER: ada estimasi harga ──
// //             Notification::send(
// //                 userId: $survey->customer_id,
// //                 title: 'Estimasi Harga Tersedia!',
// //                 body: "{$request->user()->name} telah mengisi estimasi harga surveymu sebesar Rp " .
// //                     number_format($totalEstimated, 0, ',', '.') .
// //                     ". Silakan cek dan setujui.",
// //                 type: 'survey',
// //                 notifiable: $survey,
// //             );

// //             DB::commit();

// //                 // ← TAMBAHKAN INI
// //     \App\Services\NotificationService::surveyPricedToCustomer($survey->fresh(['surveyServices.service']));



// //             return response()->json([
// //                 'status'  => true,
// //                 'message' => 'Estimasi harga berhasil dikirim ke customer.',
// //                 'data'    => $survey->fresh()->load('surveyServices'),
// //             ]);
// //         } catch (\Exception $e) {
// //             DB::rollBack();
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Gagal menyimpan estimasi: ' . $e->getMessage(),
// //             ], 500);
// //         }
// //     }
// // }



// ///code kesekian

// // namespace App\Http\Controllers\Api\Tukang;

// // use App\Http\Controllers\Controller;
// // use App\Models\SurveyRequest;
// // use App\Models\SurveyRequestService;
// // use App\Models\UserNotification;
// // use Illuminate\Http\JsonResponse;
// // use Illuminate\Http\Request;
// // use Illuminate\Support\Facades\DB;

// // /**
// //  * JobSurveyController — Dipakai tukang untuk kelola survey yang masuk.
// //  *
// //  * Routes (prefix: /api/tukang/surveys):
// //  *   GET    /                   → index()
// //  *   GET    /{survey}           → show()
// //  *   PUT    /{survey}/accept    → accept()    ✅ FIX: kirim notif ke customer
// //  *   PUT    /{survey}/reject    → reject()    ✅ FIX: kirim notif ke customer
// //  *   PUT    /{survey}/set-price → setPrice()  ✅ FIX: kirim notif ke customer
// //  */
// // class JobSurveyController extends Controller
// // {
// //     // =========================================================
// //     // INDEX — Daftar survey yang masuk ke tukang
// //     // =========================================================

// //     public function index(Request $request): JsonResponse
// //     {
// //         $query = SurveyRequest::with([
// //             'customer:id,name,avatar',
// //             'service:id,name,thumbnail',
// //         ])->where('tukang_id', $request->user()->id);

// //         if ($request->filled('status')) {
// //             $query->where('status', $request->status);
// //         }

// //         $surveys = $query->latest()->paginate($request->get('per_page', 10));

// //         return response()->json([
// //             'status' => true,
// //             'meta'   => [
// //                 'total'        => $surveys->total(),
// //                 'current_page' => $surveys->currentPage(),
// //                 'last_page'    => $surveys->lastPage(),
// //             ],
// //             'data' => collect($surveys->items())->map(fn($s) => $this->formatSurvey($s)),
// //         ]);
// //     }

// //     // =========================================================
// //     // SHOW — Detail survey
// //     // =========================================================

// //     public function show(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
// //         }

// //         $survey->load([
// //             'customer:id,name,avatar',
// //             'service:id,name,thumbnail,description',
// //             'surveyServices.service:id,name,unit',
// //         ]);

// //         return response()->json([
// //             'status' => true,
// //             'data'   => $this->formatSurveyDetail($survey),
// //         ]);
// //     }

// //     // =========================================================
// //     // ACCEPT — Tukang terima survey
// //     // ✅ FIX: Tambah notifikasi ke customer
// //     // =========================================================

// //     public function accept(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
// //         }

// //         if ($survey->status !== 'requested') {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak bisa diterima (status: ' . $survey->status . ').',
// //             ], 422);
// //         }

// //         $survey->update(['status' => 'accepted']);

// //         $survey->load(['customer', 'service', 'tukang']);

// //         // ✅ FIX: Kirim notifikasi ke CUSTOMER — survey diterima tukang
// //         UserNotification::send(
// //             userId:     $survey->customer_id,
// //             title:      'Survey Diterima! 🎉',
// //             body:       "Tukang {$request->user()->name} telah menerima permintaan surveymu untuk layanan {$survey->service?->name}.",
// //             type:       'survey_approved',       // ← type ini yang dibaca Flutter
// //             notifiable: $survey,
// //             data: [
// //                 'survey_id'    => $survey->id,
// //                 'tukang_name'  => $request->user()->name,
// //                 'service_name' => $survey->service?->name ?? '',
// //                 'address'      => $survey->address,
// //                 'survey_date'  => $survey->survey_date?->toDateTimeString(),
// //                 'survey_fee'   => (string) ($survey->survey_fee ?? '0'),
// //                 'status'       => 'accepted',
// //             ],
// //         );

// //         return response()->json([
// //             'status'  => true,
// //             'message' => 'Survey berhasil diterima.',
// //             'data'    => $this->formatSurvey($survey->fresh()),
// //         ]);
// //     }

// //     // =========================================================
// //     // REJECT — Tukang tolak survey
// //     // ✅ FIX: Tambah notifikasi ke customer
// //     // =========================================================

// //     public function reject(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         $request->validate([
// //             'reason' => 'nullable|string|max:500',
// //         ]);

// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
// //         }

// //         if (! in_array($survey->status, ['requested', 'accepted'])) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey tidak bisa ditolak pada status ini.',
// //             ], 422);
// //         }

// //         $survey->update([
// //             'status'      => 'rejected',
// //             'tukang_notes' => $request->reason,
// //         ]);

// //         $survey->load(['service']);

// //         // ✅ FIX: Kirim notifikasi ke CUSTOMER — survey ditolak
// //         UserNotification::send(
// //             userId:     $survey->customer_id,
// //             title:      'Survey Ditolak',
// //             body:       "Maaf, tukang {$request->user()->name} tidak bisa menerima surveymu" .
// //                         ($request->reason ? ". Alasan: {$request->reason}" : '.'),
// //             type:       'survey',
// //             notifiable: $survey,
// //             data: [
// //                 'survey_id'    => $survey->id,
// //                 'tukang_name'  => $request->user()->name,
// //                 'service_name' => $survey->service?->name ?? '',
// //                 'status'       => 'rejected',
// //             ],
// //         );

// //         return response()->json([
// //             'status'  => true,
// //             'message' => 'Survey berhasil ditolak.',
// //             'data'    => $this->formatSurvey($survey->fresh()),
// //         ]);
// //     }

// //     // =========================================================
// //     // SET PRICE — Tukang isi estimasi harga
// //     // ✅ FIX: Tambah notifikasi ke customer (ini yang paling penting!)
// //     // =========================================================

// //     public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
// //     {
// //         $request->validate([
// //             'survey_fee'      => 'nullable|numeric|min:0',
// //             'estimated_days'  => 'nullable|integer|min:1',
// //             'tukang_notes'    => 'nullable|string|max:1000',
// //             'services'        => 'required|array|min:1',
// //             'services.*.service_id'      => 'required|exists:services,id',
// //             'services.*.service_name'    => 'required|string',
// //             'services.*.estimated_price' => 'required|numeric|min:0',
// //             'services.*.qty'             => 'required|integer|min:1',
// //             'services.*.notes'           => 'nullable|string',
// //         ]);

// //         if ($survey->tukang_id !== $request->user()->id) {
// //             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
// //         }

// //         if (! in_array($survey->status, ['accepted', 'on_survey'])) {
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Survey belum bisa diisi estimasi (status: ' . $survey->status . ').',
// //             ], 422);
// //         }

// //         DB::beginTransaction();
// //         try {
// //             // Hitung total estimasi
// //             $totalEstimated = collect($request->services)
// //                 ->sum(fn($s) => ($s['estimated_price'] ?? 0) * ($s['qty'] ?? 1));

// //             // Update survey
// //             $survey->update([
// //                 'status'          => 'survey_priced',
// //                 'survey_fee'      => $request->survey_fee,
// //                 'estimated_price' => $totalEstimated,
// //                 'estimated_days'  => $request->estimated_days,
// //                 'tukang_notes'    => $request->tukang_notes,
// //             ]);

// //             // Hapus estimasi lama, simpan yang baru
// //             $survey->surveyServices()->delete();

// //             $servicesData = [];
// //             foreach ($request->services as $item) {
// //                 $subtotal = ($item['estimated_price'] ?? 0) * ($item['qty'] ?? 1);
// //                 $ss = SurveyRequestService::create([
// //                     'survey_request_id' => $survey->id,
// //                     'service_id'        => $item['service_id'],
// //                     'service_name'      => $item['service_name'],
// //                     'estimated_price'   => $item['estimated_price'],
// //                     'qty'               => $item['qty'],
// //                     'subtotal'          => $subtotal,
// //                     'notes'             => $item['notes'] ?? null,
// //                 ]);
// //                 $servicesData[] = [
// //                     'id'              => $ss->id,
// //                     'service_name'    => $ss->service_name,
// //                     'estimated_price' => $ss->estimated_price,
// //                     'qty'             => $ss->qty,
// //                     'subtotal'        => $subtotal,
// //                     'notes'           => $ss->notes,
// //                 ];
// //             }

// //             $survey->load(['customer', 'service', 'surveyServices']);

// //             // ✅ FIX UTAMA: Kirim notifikasi ke CUSTOMER — estimasi sudah ada
// //             // Tanpa baris ini, customer tidak pernah tahu tukang sudah isi harga!
// //             UserNotification::send(
// //                 userId:     $survey->customer_id,
// //                 title:      'Estimasi Harga Tersedia 💰',
// //                 body:       "Tukang {$request->user()->name} sudah mengisi estimasi harga untuk surveymu. Silakan cek dan setujui!",
// //                 type:       'survey_priced',      // ← Flutter routing ke SurveyEstimationDetailPage
// //                 notifiable: $survey,
// //                 data: [
// //                     'survey_id'       => $survey->id,
// //                     'tukang_name'     => $request->user()->name,
// //                     'service_name'    => $survey->service?->name ?? '',
// //                     'address'         => $survey->address,
// //                     'survey_date'     => $survey->survey_date?->toDateTimeString(),
// //                     'survey_fee'      => (string) ($survey->survey_fee ?? '0'),
// //                     'estimated_price' => (string) $totalEstimated,
// //                     'estimated_days'  => (string) ($request->estimated_days ?? ''),
// //                     'tukang_notes'    => $request->tukang_notes ?? '',
// //                     'status'          => 'survey_priced',
// //                     'survey_services' => json_encode($servicesData),
// //                 ],
// //             );

// //             DB::commit();

// //             return response()->json([
// //                 'status'  => true,
// //                 'message' => 'Estimasi harga berhasil diisi. Customer sudah diberitahu.',
// //                 'data'    => $this->formatSurveyDetail($survey->fresh([
// //                     'customer', 'service', 'surveyServices.service',
// //                 ])),
// //             ]);
// //         } catch (\Exception $e) {
// //             DB::rollBack();
// //             return response()->json([
// //                 'status'  => false,
// //                 'message' => 'Gagal menyimpan estimasi.',
// //                 'error'   => $e->getMessage(),
// //             ], 500);
// //         }
// //     }

// //     // =========================================================
// //     // PRIVATE HELPERS
// //     // =========================================================

// //     private function formatSurvey(SurveyRequest $survey): array
// //     {
// //         return [
// //             'id'              => $survey->id,
// //             'status'          => $survey->status,
// //             'address'         => $survey->address,
// //             'survey_date'     => $survey->survey_date?->toDateTimeString(),
// //             'survey_fee'      => $survey->survey_fee,
// //             'estimated_price' => $survey->estimated_price,
// //             'estimated_days'  => $survey->estimated_days,
// //             'notes'           => $survey->notes,
// //             'tukang_notes'    => $survey->tukang_notes,
// //             'created_at'      => $survey->created_at->toDateTimeString(),
// //             'customer'        => $survey->relationLoaded('customer') ? [
// //                 'id'         => $survey->customer->id,
// //                 'name'       => $survey->customer->name,
// //                 'avatar_url' => $survey->customer->avatar
// //                     ? asset($survey->customer->avatar) : null,
// //             ] : null,
// //             'service' => $survey->relationLoaded('service') ? [
// //                 'id'            => $survey->service->id,
// //                 'name'          => $survey->service->name,
// //                 'thumbnail_url' => $survey->service->thumbnail
// //                     ? asset($survey->service->thumbnail) : null,
// //             ] : null,
// //         ];
// //     }

// //     private function formatSurveyDetail(SurveyRequest $survey): array
// //     {
// //         $data = $this->formatSurvey($survey);

// //         $data['survey_services'] = $survey->relationLoaded('surveyServices')
// //             ? $survey->surveyServices->map(fn($ss) => [
// //                 'id'              => $ss->id,
// //                 'service_id'      => $ss->service_id,
// //                 'service_name'    => $ss->service_name,
// //                 'unit'            => $ss->service?->unit,
// //                 'estimated_price' => $ss->estimated_price,
// //                 'qty'             => $ss->qty,
// //                 'subtotal'        => ($ss->estimated_price ?? 0) * $ss->qty,
// //                 'notes'           => $ss->notes,
// //             ])->toArray()
// //             : [];

// //         return $data;
// //     }
// // }




// namespace App\Http\Controllers\Api\Tukang;

// use App\Http\Controllers\Controller;
// use App\Models\SurveyRequest;
// use App\Models\SurveyRequestService;
// use App\Models\UserNotification;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class JobSurveyController extends Controller
// {
//     // GET /api/tukang/surveys
//     public function index(Request $request): JsonResponse
//     {
//         $query = SurveyRequest::with([
//             'customer:id,name,avatar',
//             'service:id,name,thumbnail',
//         ])->where('tukang_id', $request->user()->id);

//         if ($request->filled('status')) {
//             $query->where('status', $request->status);
//         }

//         $surveys = $query->latest()->paginate($request->get('per_page', 10));

//         return response()->json([
//             'status' => true,
//             'meta'   => [
//                 'total'        => $surveys->total(),
//                 'current_page' => $surveys->currentPage(),
//                 'last_page'    => $surveys->lastPage(),
//             ],
//             'data' => collect($surveys->items())->map(fn($s) => $this->formatSurvey($s)),
//         ]);
//     }

//     // GET /api/tukang/surveys/{survey}
//     public function show(Request $request, SurveyRequest $survey): JsonResponse
//     {
//         if ($survey->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
//         }

//         $survey->load([
//             'customer:id,name,avatar',
//             'service:id,name,thumbnail,description',
//             'surveyServices.service:id,name,unit',
//         ]);

//         return response()->json([
//             'status' => true,
//             'data'   => $this->formatSurveyDetail($survey),
//         ]);
//     }

//     // =========================================================
//     // ACCEPT — Tukang terima survey
//     // ✅ FIX: Load relasi dulu, kirim data lengkap ke notif
//     // =========================================================
//     public function accept(Request $request, SurveyRequest $survey): JsonResponse
//     {
//         if ($survey->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
//         }

//         if ($survey->status !== 'requested') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak bisa diterima (status: ' . $survey->status . ').',
//             ], 422);
//         }

//         $survey->update(['status' => 'accepted']);

//         // ✅ WAJIB: load relasi sebelum kirim notif
//         $survey->load(['service', 'tukang']);

//         UserNotification::send(
//             userId: $survey->customer_id,
//             title: 'Survey Diterima! 🎉',
//             body: "Tukang {$request->user()->name} telah menerima permintaan surveymu untuk layanan {$survey->service?->name}.",
//             type: 'survey_approved',
//             notifiable: $survey,
//             data: [
//                 'survey_id'    => $survey->id,
//                 'tukang_name'  => $request->user()->name,
//                 'tukang_photo' => $request->user()->tukangProfile?->photo
//                     ? asset($request->user()->tukangProfile->photo) : null,
//                 'rating'       => $request->user()->tukangProfile?->rating,
//                 'service_name' => $survey->service?->name ?? '',
//                 'address'      => $survey->address,
//                 'survey_date'  => $survey->survey_date?->toDateTimeString(),
//                 'survey_fee'   => (float) ($survey->survey_fee ?? 0),
//                 'status'       => 'accepted',
//             ],
//         );

//         return response()->json([
//             'status'  => true,
//             'message' => 'Survey berhasil diterima.',
//             'data'    => $this->formatSurvey($survey->fresh()),
//         ]);
//     }

//     // =========================================================
//     // REJECT — Tukang tolak survey
//     // =========================================================
//     public function reject(Request $request, SurveyRequest $survey): JsonResponse
//     {
//         $request->validate([
//             'reason' => 'nullable|string|max:500',
//         ]);

//         if ($survey->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
//         }

//         if (!in_array($survey->status, ['requested', 'accepted'])) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak bisa ditolak pada status ini.',
//             ], 422);
//         }

//         $survey->update([
//             'status'       => 'rejected',
//             'tukang_notes' => $request->reason,
//         ]);

//         $survey->load(['service']);

//         UserNotification::send(
//             userId: $survey->customer_id,
//             title: 'Survey Ditolak',
//             body: "Maaf, tukang {$request->user()->name} tidak bisa menerima surveymu" .
//                 ($request->reason ? ". Alasan: {$request->reason}" : '.'),
//             type: 'survey',
//             notifiable: $survey,
//             data: [
//                 'survey_id'    => $survey->id,
//                 'tukang_name'  => $request->user()->name,
//                 'service_name' => $survey->service?->name ?? '',
//                 'status'       => 'rejected',
//             ],
//         );

//         return response()->json([
//             'status'  => true,
//             'message' => 'Survey berhasil ditolak.',
//             'data'    => $this->formatSurvey($survey->fresh()),
//         ]);
//     }

//     // =========================================================
//     // SET PRICE — Tukang isi estimasi harga
//     // ✅ FIX: survey_services kirim sebagai array (bukan json string)
//     // =========================================================
//     //     public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
//     //     {
//     //         $request->validate([
//     //             'survey_fee'                         => 'nullable|numeric|min:0',
//     //             'estimated_days'                     => 'nullable|integer|min:1',
//     //             'tukang_notes'                       => 'nullable|string|max:1000',
//     //             'services'                           => 'required|array|min:1',
//     //             'services.*.service_id'              => 'required|exists:services,id',
//     //             'services.*.service_name'            => 'required|string',
//     //             'services.*.estimated_price'         => 'required|numeric|min:0',
//     //             'services.*.qty'                     => 'required|integer|min:1',
//     //             'services.*.notes'                   => 'nullable|string',
//     //         ]);

//     //         if ($survey->tukang_id !== $request->user()->id) {
//     //             return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
//     //         }

//     //         if (!in_array($survey->status, ['accepted', 'on_survey'])) {
//     //             return response()->json([
//     //                 'status'  => false,
//     //                 'message' => 'Survey belum bisa diisi estimasi (status: ' . $survey->status . ').',
//     //             ], 422);
//     //         }

//     //         DB::beginTransaction();
//     //         try {
//     //             $totalEstimated = collect($request->services)
//     //                 ->sum(fn($s) => ($s['estimated_price'] ?? 0) * ($s['qty'] ?? 1));

//     //             $survey->update([
//     //                 'status'          => 'survey_priced',
//     //                 'survey_fee'      => $request->survey_fee,
//     //                 'estimated_price' => $totalEstimated,
//     //                 'estimated_days'  => $request->estimated_days,
//     //                 'tukang_notes'    => $request->tukang_notes,
//     //             ]);

//     //             $survey->surveyServices()->delete();

//     //             $servicesData = [];
//     //             foreach ($request->services as $item) {
//     //                 $subtotal = ($item['estimated_price'] ?? 0) * ($item['qty'] ?? 1);
//     //                 $ss = SurveyRequestService::create([
//     //                     'survey_request_id' => $survey->id,
//     //                     'service_id'        => $item['service_id'],
//     //                     'service_name'      => $item['service_name'],
//     //                     'estimated_price'   => $item['estimated_price'],
//     //                     'qty'               => $item['qty'],
//     //                     'subtotal'          => $subtotal,
//     //                     'notes'             => $item['notes'] ?? null,
//     //                 ]);

//     //                 // ✅ FIX: kirim sebagai array of objects, BUKAN json_encode
//     //                 $servicesData[] = [
//     //                     'id'              => $ss->id,
//     //                     'service_name'    => $ss->service_name,
//     //                     'unit'            => null,
//     //                     'estimated_price' => (float) $ss->estimated_price,
//     //                     'qty'             => (int) $ss->qty,
//     //                     'subtotal'        => (float) $subtotal,
//     //                     'notes'           => $ss->notes,
//     //                 ];
//     //             }

//     //             $survey->load(['customer', 'service', 'surveyServices']);

//     //             UserNotification::send(
//     //                 userId:     $survey->customer_id,
//     //                 title:      'Estimasi Harga Tersedia 💰',
//     //                 body:       "Tukang {$request->user()->name} sudah mengisi estimasi harga untuk surveymu. Silakan cek dan setujui!",
//     //                 type:       'survey_priced',
//     //                 notifiable: $survey,
//     //                 data: [
//     //                     'survey_id'       => $survey->id,
//     //                     'tukang_name'     => $request->user()->name,
//     //                     'tukang_photo'    => $request->user()->tukangProfile?->photo
//     //                         ? asset($request->user()->tukangProfile->photo) : null,
//     //                     'rating'          => $request->user()->tukangProfile?->rating,
//     //                     'service_name'    => $survey->service?->name ?? '',
//     //                     'address'         => $survey->address,
//     //                     'survey_date'     => $survey->survey_date?->toDateTimeString(),
//     //                     'survey_fee'      => (float) ($survey->survey_fee ?? 0),
//     //                     'estimated_price' => (float) $totalEstimated,
//     //                     'estimated_days'  => $request->estimated_days
//     //                         ? (int) $request->estimated_days : null,
//     //                     'tukang_notes'    => $request->tukang_notes ?? '',
//     //                     'status'          => 'survey_priced',
//     //                     // ✅ KRITIS: array langsung, bukan json_encode string
//     //                     'survey_services' => $servicesData,
//     //                 ],
//     //             );

//     //             DB::commit();

//     //             return response()->json([
//     //                 'status'  => true,
//     //                 'message' => 'Estimasi harga berhasil diisi. Customer sudah diberitahu.',
//     //                 'data'    => $this->formatSurveyDetail($survey->fresh([
//     //                     'customer', 'service', 'surveyServices.service',
//     //                 ])),
//     //             ]);
//     //         } catch (\Exception $e) {
//     //             DB::rollBack();
//     //             return response()->json([
//     //                 'status'  => false,
//     //                 'message' => 'Gagal menyimpan estimasi.',
//     //                 'error'   => $e->getMessage(),
//     //             ], 500);
//     //         }
//     //     }

//     //     // =========================================================
//     //     // PRIVATE HELPERS
//     //     // =========================================================
//     //     private function formatSurvey(SurveyRequest $survey): array
//     //     {
//     //         return [
//     //             'id'              => $survey->id,
//     //             'status'          => $survey->status,
//     //             'address'         => $survey->address,
//     //             'survey_date'     => $survey->survey_date?->toDateTimeString(),
//     //             'survey_fee'      => $survey->survey_fee,
//     //             'estimated_price' => $survey->estimated_price,
//     //             'estimated_days'  => $survey->estimated_days,
//     //             'notes'           => $survey->notes,
//     //             'tukang_notes'    => $survey->tukang_notes,
//     //             'created_at'      => $survey->created_at->toDateTimeString(),
//     //             'customer'        => $survey->relationLoaded('customer') ? [
//     //                 'id'         => $survey->customer->id,
//     //                 'name'       => $survey->customer->name,
//     //                 'avatar_url' => $survey->customer->avatar
//     //                     ? asset($survey->customer->avatar) : null,
//     //             ] : null,
//     //             'service' => $survey->relationLoaded('service') ? [
//     //                 'id'            => $survey->service->id,
//     //                 'name'          => $survey->service->name,
//     //                 'thumbnail_url' => $survey->service->thumbnail
//     //                     ? asset($survey->service->thumbnail) : null,
//     //             ] : null,
//     //         ];
//     //     }

//     //     private function formatSurveyDetail(SurveyRequest $survey): array
//     //     {
//     //         $data = $this->formatSurvey($survey);

//     //         $data['survey_services'] = $survey->relationLoaded('surveyServices')
//     //             ? $survey->surveyServices->map(fn($ss) => [
//     //                 'id'              => $ss->id,
//     //                 'service_id'      => $ss->service_id,
//     //                 'service_name'    => $ss->service_name,
//     //                 'unit'            => $ss->service?->unit,
//     //                 'estimated_price' => (float) $ss->estimated_price,
//     //                 'qty'             => (int) $ss->qty,
//     //                 'subtotal'        => (float) (($ss->estimated_price ?? 0) * $ss->qty),
//     //                 'notes'           => $ss->notes,
//     //             ])->toArray()
//     //             : [];

//     //         return $data;
//     //     }
//     // }



//     // public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
//     // {
//     //     $request->validate([
//     //         // Laporan hasil survey (field baru)
//     //         'damage_description'                 => 'nullable|string|max:2000',
//     //         'materials_needed'                   => 'nullable|string|max:2000',

//     //         // Field lama
//     //         'survey_fee'                         => 'nullable|numeric|min:0',
//     //         'estimated_days'                     => 'nullable|integer|min:1',
//     //         'tukang_notes'                       => 'nullable|string|max:1000',

//     //         // Rincian pekerjaan (minimal 1 item)
//     //         'services'                           => 'required|array',
//     //         'services.*.service_id'              => 'required|integer|min:1',
//     //         'services.*.service_name'            => 'required|string',
//     //         'services.*.estimated_price'         => 'required|numeric|min:0',
//     //         'services.*.qty'                     => 'required|integer|min:1',
//     //         'services.*.notes'                   => 'nullable|string',
//     //     ]);

//     //     if ($survey->tukang_id !== $request->user()->id) {
//     //         return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
//     //     }

//     //     if (!in_array($survey->status, ['accepted', 'on_survey', 'approved'])) {
//     //         return response()->json([
//     //             'status'  => false,
//     //             'message' => 'Survey belum bisa diisi estimasi (status: ' . $survey->status . ').',
//     //         ], 422);
//     //     }

//     //     DB::beginTransaction();
//     //     try {
//     //         $totalEstimated = collect($request->services)
//     //             ->sum(fn($s) => ($s['estimated_price'] ?? 0) * ($s['qty'] ?? 1));

//     //         // Update survey dengan field baru + field lama
//     //         $survey->update([
//     //             'status'              => 'survey_priced',
//     //             'damage_description'  => $request->damage_description,
//     //             'materials_needed'    => $request->materials_needed,
//     //             'survey_fee'          => $request->survey_fee,
//     //             'estimated_price'     => $totalEstimated,
//     //             'estimated_days'      => $request->estimated_days,
//     //             'tukang_notes'        => $request->tukang_notes,
//     //             'service_cost'        => $request->labor_cost ?? 0,   // ← map labor_cost → service_cost
//     //             'material_cost'       => $request->material_cost ?? 0,
//     //         ]);
//     //         // Hapus rincian lama, simpan yang baru
//     //         $survey->surveyServices()->delete();

//     //         $servicesData = [];
//     //         foreach ($request->services as $item) {
//     //             $subtotal = ($item['estimated_price'] ?? 0) * ($item['qty'] ?? 1);
//     //             $ss = SurveyRequestService::create([
//     //                 'survey_request_id' => $survey->id,
//     //                 'service_id'        => $item['service_id'],
//     //                 'service_name'      => $item['service_name'],
//     //                 'estimated_price'   => $item['estimated_price'],
//     //                 'qty'               => $item['qty'],
//     //                 'subtotal'          => $subtotal,
//     //                 'notes'             => $item['notes'] ?? null,
//     //             ]);

//     //             $servicesData[] = [
//     //                 'id'              => $ss->id,
//     //                 'service_name'    => $ss->service_name,
//     //                 'unit'            => null,
//     //                 'estimated_price' => (float) $ss->estimated_price,
//     //                 'qty'             => (int) $ss->qty,
//     //                 'subtotal'        => (float) $subtotal,
//     //                 'notes'           => $ss->notes,
//     //             ];
//     //         }

//     //         $survey->load(['customer', 'service', 'surveyServices']);

//     //         // Kirim notifikasi ke customer dengan data laporan lengkap
//     //         UserNotification::send(
//     //             userId: $survey->customer_id,
//     //             title: 'Laporan Survey Tersedia 📋',
//     //             body: "Tukang {$request->user()->name} telah menyelesaikan survey dan mengirim laporan + estimasi harga. Silakan cek dan putuskan!",
//     //             type: 'survey_priced',
//     //             notifiable: $survey,
//     //             data: [
//     //                 // Identitas
//     //                 'survey_id'          => $survey->id,
//     //                 'tukang_name'        => $request->user()->name,
//     //                 'tukang_photo'       => $request->user()->tukangProfile?->photo
//     //                     ? asset($request->user()->tukangProfile->photo) : null,
//     //                 'rating'             => $request->user()->tukangProfile?->rating,
//     //                 'service_name'       => $survey->service?->name ?? '',
//     //                 'address'            => $survey->address,
//     //                 'survey_date'        => $survey->survey_date?->toDateTimeString(),

//     //                 // Laporan hasil survey (field baru)
//     //                 'damage_description' => $request->damage_description ?? '',
//     //                 'materials_needed'   => $request->materials_needed ?? '',

//     //                 // Estimasi
//     //                 'survey_fee'         => (float) ($survey->survey_fee ?? 0),
//     //                 'estimated_price'    => (float) $totalEstimated,
//     //                 'estimated_days'     => $request->estimated_days
//     //                     ? (int) $request->estimated_days : null,
//     //                 'tukang_notes'       => $request->tukang_notes ?? '',
//     //                 'status'             => 'survey_priced',

//     //                 // Rincian pekerjaan (array, bukan json string)
//     //                 'survey_services'    => $servicesData,
//     //             ],
//     //         );

//     //         DB::commit();

//     //         return response()->json([
//     //             'status'  => true,
//     //             'message' => 'Laporan survey dan estimasi harga berhasil dikirim ke customer.',
//     //             'data'    => $this->formatSurveyDetail($survey->fresh([
//     //                 'customer',
//     //                 'service',
//     //                 'surveyServices.service',
//     //             ])),
//     //         ]);
//     //     } catch (\Exception $e) {
//     //         DB::rollBack();
//     //         return response()->json([
//     //             'status'  => false,
//     //             'message' => 'Gagal menyimpan laporan survey.',
//     //             'error'   => $e->getMessage(),
//     //         ], 500);
//     //     }
//     // }



//     public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
//     {
//         $request->validate([
//             'damage_description'         => 'nullable|string|max:2000',
//             'materials_needed'           => 'nullable|string|max:2000',
//             'survey_fee'                 => 'nullable|numeric|min:0',
//             'estimated_days'             => 'nullable|integer|min:1',
//             'tukang_notes'               => 'nullable|string|max:1000',
//             'services'                   => 'required|array|min:1',
//             'services.*.service_id'      => 'required|integer|min:1',
//             'services.*.service_name'    => 'required|string',
//             'services.*.estimated_price' => 'required|numeric|min:0',
//             'services.*.qty'             => 'required|integer|min:1',
//             'services.*.notes'           => 'nullable|string',
//         ]);

//         if ($survey->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak ditemukan.',
//             ], 404);
//         }

//         if (!in_array($survey->status, ['accepted', 'on_survey', 'approved'])) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey belum bisa diisi estimasi (status: ' . $survey->status . ').',
//             ], 422);
//         }

//         DB::beginTransaction();
//         try {
//             $totalEstimated = collect($request->services)
//                 ->sum(fn($s) => ($s['estimated_price'] ?? 0) * ($s['qty'] ?? 1));

//             // ✅ Cek kolom yang tersedia sebelum update
//             $updateData = [
//                 'status'             => 'survey_priced',
//                 'estimated_price'    => $totalEstimated,
//                 'damage_description' => $request->damage_description,
//                 'materials_needed'   => $request->materials_needed,
//                 'survey_fee'         => $request->survey_fee,
//                 'estimated_days'     => $request->estimated_days,
//                 'tukang_notes'       => $request->tukang_notes,
//                 'service_cost'       => $request->labor_cost ?? 0,
//                 'material_cost'      => $request->material_cost ?? 0,
//             ];

//             $survey->update($updateData);

//             // Hapus rincian lama
//             $survey->surveyServices()->delete();

//             // ✅ Cek apakah kolom subtotal ada di survey_request_services
//             $hasSubtotal = \Illuminate\Support\Facades\Schema::hasColumn(
//                 'survey_request_services',
//                 'subtotal'
//             );

//             $servicesData = [];
//             foreach ($request->services as $item) {
//                 $subtotal = ($item['estimated_price'] ?? 0) * ($item['qty'] ?? 1);

//                 $createData = [
//                     'survey_request_id' => $survey->id,
//                     'service_id'        => $item['service_id'],
//                     'service_name'      => $item['service_name'],
//                     'estimated_price'   => $item['estimated_price'],
//                     'qty'               => $item['qty'],
//                     'notes'             => $item['notes'] ?? null,
//                 ];

//                 // ✅ Hanya tambah subtotal jika kolomnya ada
//                 if ($hasSubtotal) {
//                     $createData['subtotal'] = $subtotal;
//                 }

//                 $ss = SurveyRequestService::create($createData);

//                 $servicesData[] = [
//                     'id'              => $ss->id,
//                     'service_name'    => $ss->service_name,
//                     'unit'            => null,
//                     'estimated_price' => (float) $ss->estimated_price,
//                     'qty'             => (int) $ss->qty,
//                     'subtotal'        => (float) $subtotal,
//                     'notes'           => $ss->notes,
//                 ];
//             }

//             $survey->load(['customer', 'service', 'surveyServices']);

//             // Kirim notifikasi ke customer
//             UserNotification::send(
//                 userId: $survey->customer_id,
//                 title: 'Laporan Survey Tersedia 📋',
//                 body: "Tukang {$request->user()->name} telah menyelesaikan survey dan mengirim laporan + estimasi harga. Silakan cek dan putuskan!",
//                 type: 'survey_priced',
//                 notifiable: $survey,
//                 data: [
//                     'survey_id'          => $survey->id,
//                     'tukang_name'        => $request->user()->name,
//                     'tukang_photo'       => $request->user()->tukangProfile?->photo
//                         ? asset($request->user()->tukangProfile->photo) : null,
//                     'rating'             => $request->user()->tukangProfile?->rating,
//                     'service_name'       => $survey->service?->name ?? '',
//                     'address'            => $survey->address,
//                     'survey_date'        => $survey->survey_date?->toDateTimeString(),
//                     'damage_description' => $request->damage_description ?? '',
//                     'materials_needed'   => $request->materials_needed ?? '',
//                     'survey_fee'         => (float) ($survey->survey_fee ?? 0),
//                     'estimated_price'    => (float) $totalEstimated,
//                     'estimated_days'     => $request->estimated_days ? (int) $request->estimated_days : null,
//                     'tukang_notes'       => $request->tukang_notes ?? '',
//                     'status'             => 'survey_priced',
//                     'survey_services'    => $servicesData,
//                 ],
//             );

//             DB::commit();

//             return response()->json([
//                 'status'  => true,
//                 'message' => 'Laporan survey dan estimasi harga berhasil dikirim ke customer.',
//                 'data'    => $this->formatSurveyDetail($survey->fresh([
//                     'customer',
//                     'service',
//                     'surveyServices.service',
//                 ])),
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Gagal menyimpan laporan survey.',
//                 'error'   => $e->getMessage(), // ← lihat pesan ini di response untuk debug
//             ], 500);
//         }
//     }

//     // =========================================================
//     // PRIVATE HELPERS
//     // =========================================================
//     private function formatSurvey(SurveyRequest $survey): array
//     {
//         return [
//             'id'                  => $survey->id,
//             'status'              => $survey->status,
//             'address'             => $survey->address,
//             'survey_date'         => $survey->survey_date?->toDateTimeString(),
//             'survey_fee'          => $survey->survey_fee,
//             'estimated_price'     => $survey->estimated_price,
//             'estimated_days'      => $survey->estimated_days,
//             'notes'               => $survey->notes,
//             'tukang_notes'        => $survey->tukang_notes,
//             // Field baru
//             'damage_description'  => $survey->damage_description,
//             'materials_needed'    => $survey->materials_needed,
//             'created_at'          => $survey->created_at->toDateTimeString(),
//             'customer'        => $survey->relationLoaded('customer') ? [
//                 'id'         => $survey->customer->id,
//                 'name'       => $survey->customer->name,
//                 'avatar_url' => $survey->customer->avatar
//                     ? asset($survey->customer->avatar) : null,
//             ] : null,
//             'service' => $survey->relationLoaded('service') ? [
//                 'id'            => $survey->service->id,
//                 'name'          => $survey->service->name,
//                 'thumbnail_url' => $survey->service->thumbnail
//                     ? asset($survey->service->thumbnail) : null,
//             ] : null,
//         ];
//     }

//     private function formatSurveyDetail(SurveyRequest $survey): array
//     {
//         $data = $this->formatSurvey($survey);

//         $data['survey_services'] = $survey->relationLoaded('surveyServices')
//             ? $survey->surveyServices->map(fn($ss) => [
//                 'id'              => $ss->id,
//                 'service_id'      => $ss->service_id,
//                 'service_name'    => $ss->service_name,
//                 'unit'            => $ss->service?->unit,
//             'estimated_price' => (float) $ss->estimated_price,
//             'qty'             => (int) $ss->qty,
//             'subtotal'        => (float) (($ss->estimated_price ?? 0) * $ss->qty),
//                 'notes'           => $ss->notes,
//             ])->toArray()
//             : [];

//         return $data;
//     }



//     public function inputEstimation(Request $request, int $id): JsonResponse
//     {
//         $validator = Validator::make($request->all(), [
//             'material_cost'      => 'required|numeric|min:0',
//             'service_cost'       => 'required|numeric|min:0',
//             'duration_days'      => 'required|integer|min:1',
//             'notes'              => 'nullable|string|max:1000',
//             'damage_description' => 'nullable|string|max:2000',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Validasi gagal',
//                 'errors'  => $validator->errors(),
//             ], 422);
//         }

//         // Ambil survey milik tukang yang login
//         $survey = SurveyRequest::where('id', $id)
//             ->where('tukang_id', auth()->id())
//             ->first();

//         if (!$survey) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak ditemukan atau bukan milik Anda',
//             ], 404);
//         }

//         // Hanya bisa input estimasi jika status = 'surveyed'
//         if ($survey->status !== 'surveyed') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => "Status survey saat ini '{$survey->status}'. Estimasi hanya bisa diinput saat status 'surveyed'.",
//             ], 422);
//         }

//         DB::beginTransaction();
//         try {
//             $totalCost = (float) $request->material_cost + (float) $request->service_cost;

//             $survey->update([
//                 'status'              => 'survey_priced',
//                 'damage_description'  => $request->damage_description,
//                 'materials_needed'    => $request->materials_needed,
//                 'survey_fee'          => $request->survey_fee,
//                 'estimated_price'     => $totalEstimated,
//                 'estimated_days'      => $request->estimated_days,
//                 'tukang_notes'        => $request->tukang_notes,
//                 'labor_cost'          => $request->labor_cost ?? 0,
//                 'material_cost'       => $request->material_cost ?? 0,
//             ]);

//             // ── Kirim notifikasi ke customer ────────────────────
//             // Sesuaikan dengan cara notif yang sudah kamu pakai.
//             // Pilihan 1: pakai NotificationHelper yang sudah ada
//             // NotificationHelper::send(
//             //     userId:  $survey->user_id,
//             //     type:    'estimation_sent',
//             //     title:   'Estimasi Pekerjaan Tersedia',
//             //     body:    'Tukang telah mengirimkan estimasi biaya. Silakan cek dan beri persetujuan.',
//             //     data:    ['survey_id' => $survey->id, 'estimated_price' => $totalCost],
//             // );

//             // Pilihan 2: pakai UserNotification model langsung (sesuai struktur model kamu)
//             \App\Models\UserNotification::create([
//                 'user_id'    => $survey->user_id,
//                 'type'       => 'estimation_sent',
//                 'title'      => 'Estimasi Pekerjaan Tersedia',
//                 'body'       => 'Tukang telah mengirimkan estimasi biaya untuk survey Anda. Silakan cek dan beri persetujuan.',
//                 'data'       => json_encode([
//                     'survey_id'       => $survey->id,
//                     'estimated_price' => $totalCost,
//                     'estimated_days'  => (int) $request->duration_days,
//                 ]),
//                 'is_read'    => false,
//             ]);

//             DB::commit();

//             return response()->json([
//                 'status'  => true,
//                 'message' => 'Estimasi berhasil dikirim ke customer',
//                 'data'    => [
//                     'survey_id'          => $survey->id,
//                     'material_cost'      => (float) $survey->material_cost,
//                     'service_cost'       => (float) $survey->service_cost,
//                     'estimated_price'    => $totalCost,
//                     'estimated_days'     => $survey->estimated_days,
//                     'tukang_notes'       => $survey->tukang_notes,
//                     'damage_description' => $survey->damage_description,
//                     'status'             => $survey->status,
//                 ],
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

//     // ─────────────────────────────────────────────────────────────
//     // GET /api/tukang/survey/{id}/estimation
//     // Preview estimasi yang sudah diinput tukang
//     // ─────────────────────────────────────────────────────────────
//     public function getEstimation(int $id): JsonResponse
//     {
//         $survey = SurveyRequest::where('id', $id)
//             ->where('tukang_id', auth()->id())
//             ->first();

//         if (!$survey) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak ditemukan',
//             ], 404);
//         }

//         return response()->json([
//             'status' => true,
//             'data'   => [
//                 'survey_id'          => $survey->id,
//                 'address'            => $survey->address,
//                 'material_cost'      => (float) ($survey->material_cost ?? 0),
//                 'service_cost'       => (float) ($survey->service_cost ?? 0),
//                 'estimated_price'    => (float) ($survey->estimated_price ?? 0),
//                 'estimated_days'     => $survey->estimated_days,
//                 'tukang_notes'       => $survey->tukang_notes,
//                 'damage_description' => $survey->damage_description,
//                 'status'             => $survey->status,
//             ],
//         ]);
//     }

//     // Di dalam JobSurveyController — ganti method approve yang ada
//     // public function approve(Request $request, SurveyRequest $survey): JsonResponse
//     // {
//     //     if ($survey->tukang_id !== $request->user()->id) {
//     //         return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
//     //     }

//     //     $survey->update(['status' => 'approved']);

//     //     return response()->json([
//     //         'status'  => true,
//     //         'message' => 'Survey berhasil diapprove.',
//     //         'data'    => $this->formatSurvey($survey->fresh()),
//     //     ]);
//     // }

//     public function approve(Request $request, SurveyRequest $survey): JsonResponse
//     {
//         // Validasi ownership
//         if ($survey->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak ditemukan.',
//             ], 404);
//         }

//         // Hanya bisa approve jika status accepted
//         if ($survey->status !== 'accepted') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Survey tidak bisa diapprove (status: ' . $survey->status . ').',
//             ], 422);
//         }

//         // Validasi body
//         $request->validate([
//             'survey_fee'   => 'required|numeric|min:0',
//             'tukang_notes' => 'nullable|string|max:500',
//         ]);

//         // Update survey — survey_fee wajib ada di $fillable model
//         $survey->update([
//             'status'       => 'approved',
//             'survey_fee'   => $request->survey_fee,   // ← ini yang null sebelumnya
//             'tukang_notes' => $request->tukang_notes,
//         ]);

//         // Load relasi untuk notif dan response
//         $survey->load(['service', 'customer']);

//         // Kirim notifikasi ke customer
//         // Customer perlu tahu ada biaya survey yang harus dibayar
//         UserNotification::create([
//             'user_id'         => $survey->customer_id,
//             'type'            => 'survey_approved',
//             'title'           => 'Survey Diapprove 🎉',
//             'body'            => "Tukang {$request->user()->name} menerima surveemu. "
//                 . "Biaya kunjungan survey: Rp " . number_format($request->survey_fee, 0, ',', '.') . ".",
//             'notifiable_type' => SurveyRequest::class,
//             'notifiable_id'   => $survey->id,
//             'data'            => json_encode([
//                 'survey_id'    => $survey->id,
//                 'tukang_name'  => $request->user()->name,
//                 'service_name' => $survey->service?->name ?? '',
//                 'address'      => $survey->address,
//                 'survey_date'  => $survey->survey_date?->toDateTimeString(),
//                 'survey_fee'   => (float) $request->survey_fee,
//                 'status'       => 'approved',
//             ]),
//             'is_read'         => false,
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Survey berhasil diapprove.',
//             'data'    => $this->formatSurvey($survey->fresh()),
//         ]);
//     }

// public function startSurvey(Request $request, $id)
// {
//     $survey = SurveyRequest::where('tukang_id', auth()->id())
//         ->where('id', $id)
//         ->where('status', 'scheduled')
//         ->firstOrFail();

//     $survey->update(['status' => 'on_survey']);

//     return response()->json([
//         'success' => true,
//         'message' => 'Survey dimulai. Selamat bekerja!',
//         'data'    => new SurveyRequestResource($survey->load(['customer', 'service'])),
//     ]);
// }

// // Selesai survey — ubah status on_survey → completed
// public function finishSurvey(Request $request, $id)
// {
//     $survey = SurveyRequest::where('tukang_id', auth()->id())
//         ->where('id', $id)
//         ->where('status', 'on_survey')
//         ->firstOrFail();

//     $data = ['status' => 'completed'];
//     if ($request->filled('notes')) {
//         $data['tukang_notes'] = $request->notes;
//     }

//     $survey->update($data);

//     // Notifikasi ke customer bahwa survey selesai
//     UserNotification::send(
//         userId:     $survey->user_id,
//         title:      'Survey Selesai',
//         body:       'Partner telah selesai melakukan survey. Estimasi biaya akan segera dikirimkan.',
//         type:       'survey_completed',
//         notifiable: $survey,
//         data:       ['survey_id' => $survey->id],
//     );

//     return response()->json([
//         'success' => true,
//         'message' => 'Survey selesai! Silakan input estimasi pekerjaan.',
//         'data'    => new SurveyRequestResource($survey->load(['customer', 'service'])),
//     ]);
// }


//     // ─────────────────────────────────────────────────────────────
//     // USE STATEMENTS yang perlu ditambahkan di atas class
//     // (jika belum ada di JobSurveyController)
//     // ─────────────────────────────────────────────────────────────
//     // use Illuminate\Http\JsonResponse;
//     // use Illuminate\Http\Request;
//     // use Illuminate\Support\Facades\DB;
//     // use Illuminate\Support\Facades\Validator;
//     // use App\Models\SurveyRequest;
//     // use App\Models\UserNotification;

// }



namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use App\Models\SurveyRequestService;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobSurveyController extends Controller
{
    // =========================================================
    // GET /api/partner/surveys
    // Daftar survey milik tukang
    // =========================================================
    public function index(Request $request): JsonResponse
    {
        $query = SurveyRequest::with([
            'customer:id,name,avatar',
            'service:id,name,thumbnail',
        ])->where('tukang_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $surveys = $query->latest()->paginate($request->get('per_page', 20));

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
    // GET /api/partner/surveys/{id}
    // Detail survey
    // =========================================================
    public function show(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        $survey->load([
            'customer:id,name,avatar,phone',
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
    // PUT /api/partner/surveys/{id}/accept
    // Step 1: Tukang terima survey (requested → accepted)
    // =========================================================
    public function accept(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        if ($survey->status !== 'requested') {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak bisa diterima (status: ' . $survey->status . ').',
            ], 422);
        }

        $survey->update(['status' => 'accepted']);
        $survey->load(['service', 'customer']);

        UserNotification::create([
            'user_id'         => $survey->customer_id,
            'type'            => 'survey_approved',
            'title'           => 'Survey Diterima! 🎉',
            'body'            => "Tukang {$request->user()->name} telah menerima permintaan surveymu.",
            'notifiable_type' => SurveyRequest::class,
            'notifiable_id'   => $survey->id,
            'data'            => json_encode([
                'survey_id'    => $survey->id,
                'tukang_name'  => $request->user()->name,
                'service_name' => $survey->service?->name ?? '',
                'address'      => $survey->address,
                'status'       => 'accepted',
            ]),
            'is_read' => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Survey berhasil diterima.',
            'data'    => $this->formatSurvey($survey->fresh()),
        ]);
    }

    // =========================================================
    // PUT /api/partner/surveys/{id}/reject
    // Tukang tolak survey
    // =========================================================
    public function reject(Request $request, SurveyRequest $survey): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        if (!in_array($survey->status, ['requested', 'accepted'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak bisa ditolak pada status ini (' . $survey->status . ').',
            ], 422);
        }

        $survey->update([
            'status'       => 'rejected',
            'tukang_notes' => $request->reason,
        ]);
        $survey->load(['service']);

        UserNotification::create([
            'user_id'         => $survey->customer_id,
            'type'            => 'survey_rejected',
            'title'           => 'Survey Ditolak',
            'body'            => "Maaf, tukang {$request->user()->name} tidak bisa menerima surveymu" .
                ($request->reason ? ". Alasan: {$request->reason}" : '.'),
            'notifiable_type' => SurveyRequest::class,
            'notifiable_id'   => $survey->id,
            'data'            => json_encode([
                'survey_id' => $survey->id,
                'status'    => 'rejected',
            ]),
            'is_read' => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Survey berhasil ditolak.',
            'data'    => $this->formatSurvey($survey->fresh()),
        ]);
    }

    // =========================================================
    // PUT /api/partner/surveys/{id}/approve
    // Step 2: Tukang approve + set biaya survey (accepted → approved)
    // Customer akan diminta bayar survey_fee
    // =========================================================
    public function approve(Request $request, SurveyRequest $survey): JsonResponse
    {
        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        if ($survey->status !== 'accepted') {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak bisa diapprove (status: ' . $survey->status . ').',
            ], 422);
        }

        $request->validate([
            'survey_fee'   => 'required|numeric|min:0',
            'tukang_notes' => 'nullable|string|max:500',
        ]);

        $survey->update([
            'status'       => 'approved',
            'survey_fee'   => $request->survey_fee,
            'tukang_notes' => $request->tukang_notes,
        ]);
        $survey->load(['service', 'customer']);

        UserNotification::create([
            'user_id'         => $survey->customer_id,
            'type'            => 'survey_approved',
            'title'           => 'Survey Diapprove 🎉',
            'body'            => "Tukang {$request->user()->name} menerima surveymu. "
                . "Biaya kunjungan survey: Rp " . number_format($request->survey_fee, 0, ',', '.') . ".",
            'notifiable_type' => SurveyRequest::class,
            'notifiable_id'   => $survey->id,
            'data'            => json_encode([
                'survey_id'    => $survey->id,
                'tukang_name'  => $request->user()->name,
                'service_name' => $survey->service?->name ?? '',
                'address'      => $survey->address,
                'survey_date'  => $survey->survey_date?->toDateTimeString(),
                'survey_fee'   => (float) $request->survey_fee,
                'status'       => 'approved',
            ]),
            'is_read' => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Survey berhasil diapprove. Customer akan diminta membayar biaya survey.',
            'data'    => $this->formatSurvey($survey->fresh()),
        ]);
    }

    // =========================================================
    // PATCH /api/partner/surveys/{id}/start
    // Step 3: Tukang mulai berangkat ke lokasi
    // Status yang diizinkan: scheduled, schedule (typo backend), approved
    // → on_survey
    // =========================================================
    public function startSurvey(Request $request, $id): JsonResponse
    {
        $survey = SurveyRequest::where('tukang_id', $request->user()->id)
            ->findOrFail($id);

        // Toleransi: 'schedule' (tanpa d), 'scheduled', dan 'approved'
        // karena observer backend kadang kirim 'schedule' bukan 'scheduled'
        $allowedStatuses = ['scheduled', 'schedule', 'approved'];

        if (!in_array($survey->status, $allowedStatuses)) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak bisa dimulai (status: ' . $survey->status . ').',
            ], 422);
        }

        $survey->update(['status' => 'on_survey']);
        $survey->load(['customer:id,name,avatar,phone', 'service:id,name,thumbnail']);

        return response()->json([
            'status'  => true,
            'message' => 'Survey dimulai. Selamat bekerja!',
            'data'    => $this->formatSurveyDetail($survey),
        ]);
    }

    // =========================================================
    // PATCH /api/partner/surveys/{id}/finish
    // Step 4: Tukang selesai survey di lokasi (on_survey → completed)
    // =========================================================
    public function finishSurvey(Request $request, $id): JsonResponse
    {
        $request->validate([
            'tukang_notes' => 'nullable|string|max:1000',
        ]);

        $survey = SurveyRequest::where('tukang_id', $request->user()->id)
            ->findOrFail($id);

        if ($survey->status !== 'on_survey') {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak bisa diselesaikan (status: ' . $survey->status . ').',
            ], 422);
        }

        $updateData = ['status' => 'completed'];
        if ($request->filled('tukang_notes')) {
            $updateData['tukang_notes'] = $request->tukang_notes;
        }

        $survey->update($updateData);
        $survey->load(['customer:id,name,avatar,phone', 'service:id,name,thumbnail']);

        // Notifikasi ke customer: survey selesai, estimasi segera dikirim
        UserNotification::create([
            'user_id'         => $survey->customer_id,
            'type'            => 'survey_completed',
            'title'           => 'Survey Selesai ✅',
            'body'            => 'Partner telah selesai melakukan survey. Estimasi biaya akan segera dikirimkan.',
            'notifiable_type' => SurveyRequest::class,
            'notifiable_id'   => $survey->id,
            'data'            => json_encode(['survey_id' => $survey->id]),
            'is_read'         => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Survey selesai! Silakan input estimasi pekerjaan.',
            'data'    => $this->formatSurveyDetail($survey),
        ]);
    }

    // =========================================================
    // PUT /api/partner/surveys/{id}/set-price
    // Step 5: Input laporan + estimasi pekerjaan
    // Status yang diizinkan: completed, on_survey, approved, schedule, scheduled
    // → survey_priced
    // =========================================================
    public function setPrice(Request $request, SurveyRequest $survey): JsonResponse
    {
        $request->validate([
            'damage_description'         => 'nullable|string|max:2000',
            'materials_needed'           => 'nullable|string|max:2000',
            'survey_fee'                 => 'nullable|numeric|min:0',
            'estimated_days'             => 'nullable|integer|min:1',
            'tukang_notes'               => 'nullable|string|max:1000',
            'labor_cost'                 => 'nullable|numeric|min:0',
            'material_cost'              => 'nullable|numeric|min:0',
            'estimated_price'            => 'nullable|numeric|min:0',
            'services'                   => 'required|array|min:1',
            'services.*.service_id'      => 'required|integer|min:1',
            'services.*.service_name'    => 'required|string',
            'services.*.estimated_price' => 'required|numeric|min:0',
            'services.*.qty'             => 'required|integer|min:1',
            'services.*.notes'           => 'nullable|string',
        ]);

        if ($survey->tukang_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Survey tidak ditemukan.'], 404);
        }

        // ✅ FIX UTAMA: tambah 'schedule' (tanpa d) dan semua status pasca-approval
        $allowedStatuses = [
            'completed',   // alur normal setelah finishSurvey
            'on_survey',   // jika langsung input tanpa klik selesai
            'approved',    // jika backend tidak update ke scheduled
            'scheduled',   // standar
            'schedule',    // typo dari observer backend (approved → schedule)
        ];

        if (!in_array($survey->status, $allowedStatuses)) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey belum bisa diisi estimasi (status: ' . $survey->status . ').',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalEstimated = collect($request->services)
                ->sum(fn($s) => ($s['estimated_price'] ?? 0) * ($s['qty'] ?? 1));

            // Pakai estimated_price dari request jika dikirim, fallback ke kalkulasi
            $finalEstimated = $request->filled('estimated_price')
                ? (float) $request->estimated_price
                : $totalEstimated;

            $updateData = [
                'status'             => 'survey_priced',
                'estimated_price'    => $finalEstimated,
                'estimated_days'     => $request->estimated_days,
                'tukang_notes'       => $request->tukang_notes,
            ];

            // Field opsional — hanya update jika dikirim
            if ($request->filled('damage_description')) {
                $updateData['damage_description'] = $request->damage_description;
            }
            if ($request->filled('survey_fee')) {
                $updateData['survey_fee'] = $request->survey_fee;
            }
            if ($request->filled('labor_cost')) {
                // Coba labor_cost dulu, fallback ke service_cost (nama kolom lama)
                $col = Schema::hasColumn('survey_requests', 'labor_cost')
                    ? 'labor_cost'
                    : (Schema::hasColumn('survey_requests', 'service_cost') ? 'service_cost' : null);
                if ($col) $updateData[$col] = $request->labor_cost;
            }
            if ($request->filled('material_cost')) {
                if (Schema::hasColumn('survey_requests', 'material_cost')) {
                    $updateData['material_cost'] = $request->material_cost;
                }
            }

            $survey->update($updateData);

            // Hapus services lama, simpan yang baru
            $survey->surveyServices()->delete();

            $hasSubtotal = Schema::hasColumn('survey_request_services', 'subtotal');
            $servicesData = [];

            foreach ($request->services as $item) {
                $subtotal = ($item['estimated_price'] ?? 0) * ($item['qty'] ?? 1);

                $createData = [
                    'survey_request_id' => $survey->id,
                    'service_id'        => $item['service_id'],
                    'service_name'      => $item['service_name'],
                    'estimated_price'   => $item['estimated_price'],
                    'qty'               => $item['qty'],
                    'notes'             => $item['notes'] ?? null,
                ];

                if ($hasSubtotal) {
                    $createData['subtotal'] = $subtotal;
                }

                $ss = SurveyRequestService::create($createData);

                $servicesData[] = [
                    'id'              => $ss->id,
                    'service_name'    => $ss->service_name,
                    'unit'            => null,
                    'estimated_price' => (float) $ss->estimated_price,
                    'qty'             => (int) $ss->qty,
                    'subtotal'        => (float) $subtotal,
                    'notes'           => $ss->notes,
                ];
            }

            $survey->load(['customer', 'service', 'surveyServices']);

            // Notifikasi ke customer
            UserNotification::create([
                'user_id'         => $survey->customer_id,
                'type'            => 'survey_priced',
                'title'           => 'Laporan Survey Tersedia 📋',
                'body'            => "Tukang {$request->user()->name} telah menyelesaikan survey dan mengirim estimasi harga. Silakan cek dan putuskan!",
                'notifiable_type' => SurveyRequest::class,
                'notifiable_id'   => $survey->id,
                'data'            => json_encode([
                    'survey_id'          => $survey->id,
                    'tukang_name'        => $request->user()->name,
                    'service_name'       => $survey->service?->name ?? '',
                    'address'            => $survey->address,
                    'damage_description' => $request->damage_description ?? '',
                    'estimated_price'    => (float) $finalEstimated,
                    'estimated_days'     => $request->estimated_days ? (int) $request->estimated_days : null,
                    'tukang_notes'       => $request->tukang_notes ?? '',
                    'status'             => 'survey_priced',
                    'survey_services'    => $servicesData,
                ]),
                'is_read' => false,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Laporan survey dan estimasi harga berhasil dikirim ke customer.',
                'data'    => $this->formatSurveyDetail($survey->fresh([
                    'customer',

                    
                    
                    'service',
                    'surveyServices.service',
                ])),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan laporan survey.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function formatSurvey(SurveyRequest $survey): array
    {
        return [
            'id'                 => $survey->id,
            'status'             => $survey->status,
            'address'            => $survey->address,
            'latitude'           => $survey->latitude,
            'longitude'          => $survey->longitude,
            'survey_date'        => $survey->survey_date?->toDateTimeString(),
            'scheduled_at'       => $survey->scheduled_at?->toDateTimeString(),
            'survey_fee'         => (float) ($survey->survey_fee ?? 0),
            'estimated_price'    => (float) ($survey->estimated_price ?? 0),
            'estimated_days'     => $survey->estimated_days,
            'notes'              => $survey->notes,
            'tukang_notes'       => $survey->tukang_notes,
            'damage_description' => $survey->damage_description ?? null,
            'labor_cost'         => (float) ($survey->labor_cost ?? $survey->service_cost ?? 0),
            'material_cost'      => (float) ($survey->material_cost ?? 0),
            'created_at'         => $survey->created_at?->toDateTimeString(),
            'updated_at'         => $survey->updated_at?->toDateTimeString(),
            'customer'           => $survey->relationLoaded('customer') ? [
                'id'         => $survey->customer->id,
                'name'       => $survey->customer->name,
                'phone'      => $survey->customer->phone ?? null,
                'avatar_url' => $survey->customer->avatar
                    ? asset($survey->customer->avatar) : null,
            ] : null,
            'service' => $survey->relationLoaded('service') ? [
                'id'            => $survey->service->id,
                'name'          => $survey->service->name,
                'thumbnail_url' => $survey->service->thumbnail
                    ? asset($survey->service->thumbnail) : null,
            ] : null,
        ];
    }

    private function formatSurveyDetail(SurveyRequest $survey): array
    {
        $data = $this->formatSurvey($survey);

        $data['survey_services'] = $survey->relationLoaded('surveyServices')
            ? $survey->surveyServices->map(fn($ss) => [
                'id'              => $ss->id,
                'service_id'      => $ss->service_id,
                'service_name'    => $ss->service_name,
                'unit'            => $ss->service?->
                unit,
    
                'estimated_price' => (float) $ss->estimated_price,
            'qty'             => (int) $ss->qty,
            'subtotal'        => (float) (($ss->estimated_price ?? 0) * $ss->qty),
                'notes'           => $ss->notes,
            ])->toArray()
            : [];

        $data['order'] = $survey->relationLoaded('order') && $survey->order ? [
            'id'           => $survey->order->id,
            'order_number' => $survey->order->order_number,
            'status'       => $survey->order->status,
        ] : null;

        return $data;
    }
}
