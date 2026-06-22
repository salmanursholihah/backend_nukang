<?php



namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Service;
use App\Models\SurveyRequest;
use App\Models\TukangProfile;
use App\Models\UserNotification;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ImageUploadTrait;

    // =========================================================
    // INDEX — Daftar order milik customer
    // GET /api/customer/orders
    // =========================================================
    public function index(Request $request): JsonResponse
    {
        $query = Order::with([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating,is_verified',
            'details.service:id,name,thumbnail',
            'payment:id,order_id,status,method,amount',
        ])->where('customer_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'meta' => [
                'total'        => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
            'data' => collect($orders->items())->map(fn($o) => $this->formatOrder($o)),
        ]);
    }

    // =========================================================
    // STORE — Buat order baru (langsung tanpa survey)
    // POST /api/customer/orders
    // =========================================================
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tukang_id'          => 'required|exists:users,id',
            'address'            => 'required|string',
            'latitude'           => 'required|numeric',
            'longitude'          => 'required|numeric',
            'service_date'       => 'required|date|after:now',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        $tukangProfile = TukangProfile::where('user_id', $request->tukang_id)
            ->where('is_available', true)
            ->where('is_verified', true)
            ->first();

        if (!$tukangProfile) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang tidak tersedia saat ini.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $subtotal   = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $service = Service::find($item['service_id']);

                if (!$service || !$service->is_active) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Service '{$service?->name}' tidak tersedia.",
                    ], 422);
                }

                $tukangService = DB::table('tukang_services')
                    ->where('tukang_id', $request->tukang_id)
                    ->where('service_id', $item['service_id'])
                    ->first();

                $price        = $tukangService?->custom_price ?? $service->base_price ?? 0;
                $itemSubtotal = $price * $item['qty'];
                $subtotal    += $itemSubtotal;

                $orderItems[] = [
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'price'        => $price,
                    'qty'          => $item['qty'],
                    'subtotal'     => $itemSubtotal,
                ];
            }

            $serviceFee = $subtotal * 0.10;
            $totalPrice = $subtotal + $serviceFee;

            $order = Order::create([
                'customer_id'  => $request->user()->id,
                'tukang_id'    => $request->tukang_id,
                'address'      => $request->address,
                'latitude'     => $request->latitude,
                'longitude'    => $request->longitude,
                'service_date' => $request->service_date,
                'notes'        => $request->notes,
                'subtotal'     => $subtotal,
                'service_fee'  => $serviceFee,
                'total_price'  => $totalPrice,
                'status'       => 'pending',
            ]);

            foreach ($orderItems as $item) {
                OrderDetail::create(array_merge(['order_id' => $order->id], $item));
            }

            DB::commit();

            $order->load([
                'tukang:id,name,avatar',
                'tukang.tukangProfile:user_id,photo,rating',
                'details.service:id,name,thumbnail',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Order berhasil dibuat. Menunggu konfirmasi tukang.',
                'data'    => $this->formatOrder($order),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat order.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // SHOW — Detail order
    // GET /api/customer/orders/{order}
    // =========================================================
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $order->load([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating,is_verified,city',
            'tukang.tukangLocation:tukang_id,latitude,longitude,is_online',
            'details.service:id,name,thumbnail',
            'progresses',
            'payment',
            'review',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $this->formatOrderDetail($order),
        ]);
    }

    // =========================================================
    // CANCEL — Batalkan order
    // DELETE /api/customer/orders/{order}
    // =========================================================
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if (!in_array($order->status, ['pending', 'accepted'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak bisa dibatalkan karena sudah dalam proses pengerjaan.',
            ], 422);
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        $order->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Order berhasil dibatalkan.',
            'data'    => $this->formatOrder($order->fresh()),
        ]);
    }

    // =========================================================
    // PROGRESSES
    // GET /api/customer/orders/{order}/progresses
    // =========================================================
    public function progresses(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $order->load('progresses');

        return response()->json([
            'status' => true,
            'data'   => $order->progresses->map(fn($p) => [
                'id'          => $p->id,
                'title'       => $p->title,
                'description' => $p->description,
                'photo_url'   => $p->photo ? asset($p->photo) : null,
                'created_at'  => $p->created_at->toDateTimeString(),
            ]),
        ]);
    }

    // =========================================================
    // PAYMENT DETAIL
    // GET /api/customer/orders/{order}/payment
    // =========================================================
    public function paymentDetail(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $order->load('payment');

        return response()->json([
            'status' => true,
            'data'   => [
                'order_number' => $order->order_number,
                'total_price'  => $order->total_price,
                'payment'      => $order->payment ? [
                    'id'              => $order->payment->id,
                    'status'          => $order->payment->status,
                    'method'          => $order->payment->method,
                    'payment_channel' => $order->payment->payment_channel,
                    'amount'          => $order->payment->amount,
                    'snap_token'      => $order->payment->snap_token,
                    'paid_at'         => $order->payment->paid_at?->toDateTimeString(),
                ] : null,
            ],
        ]);
    }

    // =========================================================
    // CREATE BOOKING — Order dari survey (setelah approve estimasi)
    // POST /api/customer/orders/booking
    // =========================================================
    public function createBooking(Request $request): JsonResponse
    {
        $request->validate([
            'survey_id' => 'required|integer|exists:survey_requests,id',
        ]);

        // ✅ Cek survey milik customer dan statusnya approved
        $survey = SurveyRequest::where('id', $request->survey_id)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (!$survey) {
            return response()->json([
                'status'  => false,
                'message' => 'Survey tidak ditemukan.',
            ], 404);
        }

        // ✅ Survey harus approved (sudah setujui estimasi di SurveyRequestController::approve)
        if ($survey->status !== 'approved') {
            return response()->json([
                'status'  => false,
                'message' => "Survey belum disetujui. Status saat ini: '{$survey->status}'",
            ], 422);
        }

        // ✅ Cek apakah order sudah dibuat dari survey ini
        $existingOrder = Order::where('survey_request_id', $survey->id)->first();
        if ($existingOrder) {
            return response()->json([
                'status'  => true,
                'message' => 'Order sudah dibuat sebelumnya.',
                'data'    => [
                    'id'           => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'total_price'  => (float) $existingOrder->total_price,
                    'status'       => $existingOrder->status,
                ],
            ]);
        }

        DB::beginTransaction();
        try {
            $surveyServices = $survey->surveyServices()->get();
            $subtotal       = $surveyServices->sum(fn($ss) => ($ss->estimated_price ?? 0) * $ss->qty);
            $serviceFee     = $subtotal * 0.10;
            $totalPrice     = $subtotal + $serviceFee;

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

            // Notifikasi ke tukang
            UserNotification::send(
                userId: $survey->tukang_id,
                title: 'Order Booking Dibuat',
                body: 'Customer telah membuat order dari survey. Silakan tunggu pembayaran.',
                type: 'order',
                notifiable: $order,
                data: [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'total_price'  => (string) $totalPrice,
                ],
            );

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order berhasil dibuat.',
                'data'    => [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal'     => (float) $subtotal,
                    'service_fee'  => (float) $serviceFee,
                    'total_price'  => (float) $totalPrice,
                    'status'       => $order->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat order dari survey.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // BOOKING DETAIL
    // GET /api/customer/orders/{id}/booking-detail
    // =========================================================
    public function getBookingDetail(int $id): JsonResponse
    {
        $order = Order::with(['details', 'tukang:id,name,avatar', 'surveyRequest'])
            ->where('id', $id)
            ->where('customer_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'address'      => $order->address,
                'service_date' => $order->service_date?->toDateTimeString(),
                'notes'        => $order->notes,
                'subtotal'     => (float) $order->subtotal,
                'service_fee'  => (float) $order->service_fee,
                'total_price'  => (float) $order->total_price,
                'status'       => $order->status,
                'tukang'       => $order->tukang ? [
                    'id'   => $order->tukang->id,
                    'name' => $order->tukang->name,
                ] : null,
                'items' => $order->details->map(fn($d) => [
                    'service_name' => $d->service_name,
                    'price'        => (float) $d->price,
                    'qty'          => $d->qty,
                    'subtotal'     => (float) $d->subtotal,
                ]),
            ],
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================
    private function formatOrder(Order $order): array
    {
        return [
            'id'           => $order->id,
            'order_number' => $order->order_number,
            'status'       => $order->status,
            'service_date' => $order->service_date?->toDateTimeString(),
            'address'      => $order->address,
            'subtotal'     => $order->subtotal,
            'service_fee'  => $order->service_fee,
            'total_price'  => $order->total_price,
            'notes'        => $order->notes,
            'created_at'   => $order->created_at->toDateTimeString(),
            'tukang'       => $order->tukang ? [
                'id'          => $order->tukang->id,
                'name'        => $order->tukang->name,
                'avatar_url'  => $order->tukang->avatar ? asset($order->tukang->avatar) : null,
                'photo_url'   => $order->tukang->tukangProfile?->photo
                    ? asset($order->tukang->tukangProfile->photo) : null,
                'rating'      => $order->tukang->tukangProfile?->rating,
                'is_verified' => $order->tukang->tukangProfile?->is_verified,
            ] : null,
            'items' => $order->relationLoaded('details')
                ? $order->details->map(fn($d) => [
                    'id'            => $d->id,
                    'service_name'  => $d->service_name,
                    'price'         => $d->price,
                    'qty'           => $d->qty,
                    'subtotal'      => $d->subtotal,
                    'thumbnail_url' => $d->service?->thumbnail
                        ? asset($d->service->thumbnail) : null,
                ]) : [],
            'payment_status' => $order->relationLoaded('payment')
                ? $order->payment?->status
                : null,
        ];
    }

    private function formatOrderDetail(Order $order): array
    {
        $data = $this->formatOrder($order);

        if (isset($data['tukang'])) {
            $data['tukang']['location'] = $order->tukang?->tukangLocation ? [
                'latitude'  => $order->tukang->tukangLocation->latitude,
                'longitude' => $order->tukang->tukangLocation->longitude,
                'is_online' => $order->tukang->tukangLocation->is_online,
            ] : null;
            $data['tukang']['city'] = $order->tukang?->tukangProfile?->city;
        }

        $data['progresses'] = $order->relationLoaded('progresses')
            ? $order->progresses->map(fn($p) => [
                'id'          => $p->id,
                'title'       => $p->title,
                'description' => $p->description,
                'photo_url'   => $p->photo ? asset($p->photo) : null,
                'created_at'  => $p->created_at->toDateTimeString(),
            ]) : [];

        $data['review'] = $order->relationLoaded('review') && $order->review ? [
            'id'         => $order->review->id,
            'rating'     => $order->review->rating,
            'comment'    => $order->review->comment,
            'created_at' => $order->review->created_at->toDateTimeString(),
        ] : null;

        $data['payment'] = $order->relationLoaded('payment') && $order->payment ? [
            'id'              => $order->payment->id,
            'status'          => $order->payment->status,
            'method'          => $order->payment->method,
            'payment_channel' => $order->payment->payment_channel,
            'amount'          => $order->payment->amount,
            'snap_token'      => $order->payment->snap_token,
            'paid_at'         => $order->payment->paid_at?->toDateTimeString(),
        ] : null;

        $data['cancel_reason'] = $order->cancel_reason;
        $data['started_at']    = $order->started_at?->toDateTimeString();
        $data['completed_at']  = $order->completed_at?->toDateTimeString();

        return $data;
    }
}
///code lama
// namespace App\Http\Controllers\Api\Customer;

// use App\Http\Controllers\Controller;
// use App\Models\Order;
// use App\Models\OrderDetail;
// use App\Models\Service;
// use App\Models\TukangProfile;
// use App\Traits\ImageUploadTrait;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class OrderController extends Controller
// {
//     use ImageUploadTrait;

//     // =========================================================
//     // INDEX — Daftar order milik customer
//     // GET /api/customer/orders
//     // Query params:
//     //   ?status=pending|accepted|on_progress|completed|cancelled
//     //   ?per_page=10
//     // =========================================================

//     public function index(Request $request): JsonResponse
//     {
//         $query = Order::with([
//             'tukang:id,name,avatar',
//             'tukang.tukangProfile:user_id,photo,rating,is_verified',
//             'details.service:id,name,thumbnail',
//             'payment:id,order_id,status,method,amount',
//         ])
//             ->where('customer_id', $request->user()->id);

//         // Filter by status
//         if ($request->filled('status')) {
//             $query->where('status', $request->status);
//         }

//         $orders = $query->latest()->paginate($request->get('per_page', 10));

//         return response()->json([
//             'status' => true,
//             'meta'   => [
//                 'total'        => $orders->total(),
//                 'current_page' => $orders->currentPage(),
//                 'last_page'    => $orders->lastPage(),
//             ],
//             'data' => collect($orders->items())->map(fn($o) => $this->formatOrder($o)),
//         ]);
//     }


//     // =========================================================
//     // STORE — Buat order baru
//     // POST /api/customer/orders
//     // Body:
//     //   tukang_id       : int (required)
//     //   address         : string (required)
//     //   latitude        : decimal (required)
//     //   longitude       : decimal (required)
//     //   service_date    : datetime (required)
//     //   notes           : string (optional)
//     //   items           : array (required)
//     //     - service_id  : int
//     //     - qty         : int
//     // =========================================================

//     public function store(Request $request): JsonResponse
//     {
//         $request->validate([
//             'tukang_id'           => 'required|exists:users,id',
//             'address'             => 'required|string',
//             'latitude'            => 'required|numeric',
//             'longitude'           => 'required|numeric',
//             'service_date'        => 'required|date|after:now',
//             'notes'               => 'nullable|string',
//             'items'               => 'required|array|min:1',
//             'items.*.service_id'  => 'required|exists:services,id',
//             'items.*.qty'         => 'required|integer|min:1',
//         ]);

//         // Cek tukang tersedia dan aktif
//         $tukangProfile = TukangProfile::where('user_id', $request->tukang_id)
//             ->where('is_available', true)
//             ->where('is_verified', true)
//             ->first();

//         if (! $tukangProfile) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Tukang tidak tersedia saat ini.',
//             ], 422);
//         }

//         DB::beginTransaction();
//         try {
//             // Hitung subtotal dari setiap item
//             $subtotal = 0;
//             $orderItems = [];

//             foreach ($request->items as $item) {
//                 $service = Service::find($item['service_id']);

//                 if (! $service || ! $service->is_active) {
//                     return response()->json([
//                         'status'  => false,
//                         'message' => "Service '{$service?->name}' tidak tersedia.",
//                     ], 422);
//                 }

//                 // Cek apakah tukang bisa mengerjakan service ini
//                 $tukangService = DB::table('tukang_services')
//                     ->where('tukang_id', $request->tukang_id)
//                     ->where('service_id', $item['service_id'])
//                     ->first();

//                 // Gunakan harga custom tukang jika ada, fallback ke base_price
//                 $price    = $tukangService?->custom_price ?? $service->base_price ?? 0;
//                 $itemSubtotal = $price * $item['qty'];
//                 $subtotal += $itemSubtotal;

//                 $orderItems[] = [
//                     'service_id'   => $service->id,
//                     'service_name' => $service->name,
//                     'price'        => $price,
//                     'qty'          => $item['qty'],
//                     'subtotal'     => $itemSubtotal,
//                 ];
//             }

//             $serviceFee = $subtotal * 0.10; // 10% komisi platform
//             $totalPrice = $subtotal + $serviceFee;

//             // Buat order
//             $order = Order::create([
//                 'customer_id'   => $request->user()->id,
//                 'tukang_id'     => $request->tukang_id,
//                 'address'       => $request->address,
//                 'latitude'      => $request->latitude,
//                 'longitude'     => $request->longitude,
//                 'service_date'  => $request->service_date,
//                 'notes'         => $request->notes,
//                 'subtotal'      => $subtotal,
//                 'service_fee'   => $serviceFee,
//                 'total_price'   => $totalPrice,
//                 'status'        => 'pending',
//             ]);

//             // Buat order details
//             foreach ($orderItems as $item) {
//                 OrderDetail::create(array_merge(['order_id' => $order->id], $item));
//             }

//             DB::commit();

//             $order->load([
//                 'tukang:id,name,avatar',
//                 'tukang.tukangProfile:user_id,photo,rating',
//                 'details.service:id,name,thumbnail',
//             ]);

//             return response()->json([
//                 'status'  => true,
//                 'message' => 'Order berhasil dibuat. Menunggu konfirmasi tukang.',
//                 'data'    => $this->formatOrder($order),
//             ], 201);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Gagal membuat order. Silakan coba lagi.',
//                 'error'   => $e->getMessage(),
//             ], 500);
//         }
//     }


//     // =========================================================
//     // SHOW — Detail order
//     // GET /api/customer/orders/{order}
//     // =========================================================

//     public function show(Request $request, Order $order): JsonResponse
//     {
//         // Pastikan order milik customer ini
//         if ($order->customer_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         $order->load([
//             'tukang:id,name,avatar',
//             'tukang.tukangProfile:user_id,photo,rating,is_verified,city',
//             'tukang.tukangLocation:tukang_id,latitude,longitude,is_online',
//             'details.service:id,name,thumbnail',
//             'progresses',
//             'payment',
//             'review',
//         ]);

//         return response()->json([
//             'status' => true,
//             'data'   => $this->formatOrderDetail($order),
//         ]);
//     }


//     // =========================================================
//     // CANCEL — Batalkan order
//     // DELETE /api/customer/orders/{order}
//     // Body:
//     //   cancel_reason : string (required)
//     // =========================================================

//     public function cancel(Request $request, Order $order): JsonResponse
//     {
//         if ($order->customer_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         // Hanya bisa cancel jika masih pending atau accepted
//         if (! in_array($order->status, ['pending', 'accepted'])) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak bisa dibatalkan karena sudah dalam proses pengerjaan.',
//             ], 422);
//         }

//         $request->validate([
//             'cancel_reason' => 'required|string|max:255',
//         ]);

//         $order->update([
//             'status'        => 'cancelled',
//             'cancel_reason' => $request->cancel_reason,
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Order berhasil dibatalkan.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }


//     // =========================================================
//     // PROGRESSES — Foto progress pengerjaan
//     // GET /api/customer/orders/{order}/progresses
//     // =========================================================

//     public function progresses(Request $request, Order $order): JsonResponse
//     {
//         if ($order->customer_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         $order->load('progresses');

//         return response()->json([
//             'status' => true,
//             'data'   => $order->progresses->map(fn($p) => [
//                 'id'          => $p->id,
//                 'title'       => $p->title,
//                 'description' => $p->description,
//                 'photo_url'   => $p->photo ? asset($p->photo) : null,
//                 'created_at'  => $p->created_at->toDateTimeString(),
//             ]),
//         ]);
//     }


//     // =========================================================
//     // PAYMENT DETAIL — Cek detail pembayaran order
//     // GET /api/customer/orders/{order}/payment
//     // =========================================================

//     public function paymentDetail(Request $request, Order $order): JsonResponse
//     {
//         if ($order->customer_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         $order->load('payment');

//         return response()->json([
//             'status' => true,
//             'data'   => [
//                 'order_number' => $order->order_number,
//                 'total_price'  => $order->total_price,
//                 'payment'      => $order->payment ? [
//                     'id'              => $order->payment->id,
//                     'status'          => $order->payment->status,
//                     'method'          => $order->payment->method,
//                     'payment_channel' => $order->payment->payment_channel,
//                     'amount'          => $order->payment->amount,
//                     'snap_token'      => $order->payment->snap_token,
//                     'paid_at'         => $order->payment->paid_at?->toDateTimeString(),
//                 ] : null,
//             ],
//         ]);
//     }


//     // =========================================================
//     // PRIVATE HELPERS
//     // =========================================================

//     private function formatOrder(Order $order): array
//     {
//         return [
//             'id'           => $order->id,
//             'order_number' => $order->order_number,
//             'status'       => $order->status,
//             'service_date' => $order->service_date?->toDateTimeString(),
//             'address'      => $order->address,
//             'subtotal'     => $order->subtotal,
//             'service_fee'  => $order->service_fee,
//             'total_price'  => $order->total_price,
//             'notes'        => $order->notes,
//             'created_at'   => $order->created_at->toDateTimeString(),
//             'tukang'       => $order->tukang ? [
//                 'id'         => $order->tukang->id,
//                 'name'       => $order->tukang->name,
//                 'avatar_url' => $order->tukang->avatar ? asset($order->tukang->avatar) : null,
//                 'photo_url'  => $order->tukang->tukangProfile?->photo
//                     ? asset($order->tukang->tukangProfile->photo) : null,
//                 'rating'     => $order->tukang->tukangProfile?->rating,
//                 'is_verified' => $order->tukang->tukangProfile?->is_verified,
//             ] : null,
//             'items'        => $order->relationLoaded('details')
//                 ? $order->details->map(fn($d) => [
//                     'id'           => $d->id,
//                     'service_name' => $d->service_name,
//                     'price'        => $d->price,
//                     'qty'          => $d->qty,
//                     'subtotal'     => $d->subtotal,
//                     'thumbnail_url' => $d->service?->thumbnail ? asset($d->service->thumbnail) : null,
//                 ]) : [],
//             'payment_status' => $order->relationLoaded('payment')
//                 ? $order->payment?->status
//                 : null,
//         ];
//     }

//     private function formatOrderDetail(Order $order): array
//     {
//         $data = $this->formatOrder($order);

//         // Tambahan data untuk detail
//         $data['tukang']['location'] = $order->tukang?->tukangLocation ? [
//             'latitude'  => $order->tukang->tukangLocation->latitude,
//             'longitude' => $order->tukang->tukangLocation->longitude,
//             'is_online' => $order->tukang->tukangLocation->is_online,
//         ] : null;

//         $data['tukang']['city'] = $order->tukang?->tukangProfile?->city;

//         $data['progresses'] = $order->relationLoaded('progresses')
//             ? $order->progresses->map(fn($p) => [
//                 'id'          => $p->id,
//                 'title'       => $p->title,
//                 'description' => $p->description,
//                 'photo_url'   => $p->photo ? asset($p->photo) : null,
//                 'created_at'  => $p->created_at->toDateTimeString(),
//             ]) : [];

//         $data['review'] = $order->relationLoaded('review') && $order->review ? [
//             'id'         => $order->review->id,
//             'rating'     => $order->review->rating,
//             'comment'    => $order->review->comment,
//             'tags'       => $order->review->tags,
//             'created_at' => $order->review->created_at->toDateTimeString(),
//         ] : null;

//         $data['payment'] = $order->relationLoaded('payment') && $order->payment ? [
//             'id'              => $order->payment->id,
//             'status'          => $order->payment->status,
//             'method'          => $order->payment->method,
//             'payment_channel' => $order->payment->payment_channel,
//             'amount'          => $order->payment->amount,
//             'snap_token'      => $order->payment->snap_token,
//             'paid_at'         => $order->payment->paid_at?->toDateTimeString(),
//         ] : null;

//         $data['cancel_reason']  = $order->cancel_reason;
//         $data['started_at']     = $order->started_at?->toDateTimeString();
//         $data['completed_at']   = $order->completed_at?->toDateTimeString();

//         return $data;
//     }


//     public function createBooking(Request $request): JsonResponse
// {
//     $validator = Validator::make($request->all(), [
//         'survey_id'      => 'required|integer|exists:survey_requests,id',
//         'scheduled_date' => 'required|date|after_or_equal:today',
//         'address'        => 'required|string|max:500',
//         'notes'          => 'nullable|string|max:1000',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Validasi gagal',
//             'errors'  => $validator->errors(),
//         ], 422);
//     }

//     $survey = SurveyRequest::where('id', $request->survey_id)
//         ->where('user_id', auth()->id())
//         ->first();

//     if (!$survey) {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Survey tidak ditemukan',
//         ], 404);
//     }

//     if ($survey->status !== 'estimation_approved') {
//         return response()->json([
//             'status'  => false,
//             'message' => "Estimasi belum disetujui. Status saat ini: '{$survey->status}'",
//         ], 422);
//     }

//     // Cek apakah sudah ada booking untuk survey ini
//     $existingOrder = Order::where('survey_id', $survey->id)->first();
//     if ($existingOrder) {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Booking untuk survey ini sudah ada',
//             'data'    => [
//                 'order_id' => $existingOrder->id,
//                 'status'   => $existingOrder->status,
//             ],
//         ], 422);
//     }

//     DB::beginTransaction();
//     try {
//         // Buat Order baru
//         // Sesuaikan kolom dengan struktur Order.php yang sudah ada
//         $order = Order::create([
//             'user_id'        => auth()->id(),
//             'tukang_id'      => $survey->tukang_id,
//             'survey_id'      => $survey->id,
//             'scheduled_date' => $request->scheduled_date,
//             'address'        => $request->address,
//             'notes'          => $request->notes,
//             'total_amount'   => $survey->estimated_price,
//             'status'         => 'pending_payment',
//             'type'           => 'booking', // tambahkan kolom ini jika belum ada
//         ]);

//         // Buat OrderDetail dengan rincian dari estimasi
//         OrderDetail::create([
//             'order_id'      => $order->id,
//             'material_cost' => $survey->material_cost,
//             'service_cost'  => $survey->service_cost,
//             'total_cost'    => $survey->estimated_price,
//             'duration_days' => $survey->estimated_days,
//             'notes'         => $survey->tukang_notes,
//         ]);

//         // Update status survey
//         $survey->update(['status' => 'booking_created']);

//         // Notifikasi ke tukang
//         \App\Models\UserNotification::create([
//             'user_id'  => $survey->tukang_id,
//             'type'     => 'booking_created',
//             'title'    => 'Booking Order Dibuat',
//             'body'     => 'Customer telah membuat booking order. Menunggu konfirmasi pembayaran.',
//             'data'     => json_encode([
//                 'order_id'  => $order->id,
//                 'survey_id' => $survey->id,
//             ]),
//             'is_read'  => false,
//         ]);

//         DB::commit();

//         return response()->json([
//             'status'  => true,
//             'message' => 'Booking berhasil dibuat. Silakan lanjut ke pembayaran.',
//             'data'    => [
//                 'order_id'       => $order->id,
//                 'survey_id'      => $survey->id,
//                 'scheduled_date' => $order->scheduled_date,
//                 'address'        => $order->address,
//                 'notes'          => $order->notes,
//                 'total_amount'   => (float) $order->total_amount,
//                 'status'         => $order->status,
//             ],
//         ]);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json([
//             'status'  => false,
//             'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
//         ], 500);
//     }
// }

// ///ini yang versi baru

// public function createBooking(Request $request)
// {
//     $request->validate([
//         'survey_id'      => 'required|integer|exists:survey_requests,id',
//         'scheduled_date' => 'required|date|after:today',
//         'address'        => 'required|string|max:500',
//         'notes'          => 'nullable|string|max:1000',
//     ]);

//     $survey = SurveyRequest::where('id', $request->survey_id)
//         ->where('customer_id', auth()->id())
//         ->where('status', 'estimation_approved') // hanya yang sudah di-approve
//         ->firstOrFail();

//     $booking = BookingOrder::create([
//         'survey_id'      => $survey->id,
//         'customer_id'    => auth()->id(),
//         'tukang_id'      => $survey->tukang_id,
//         'scheduled_date' => $request->scheduled_date,
//         'address'        => $request->address,
//         'notes'          => $request->notes,
//         'total_amount'   => $survey->estimated_price,
//         'status'         => 'pending_payment',
//     ]);

//     // ── Kirim notifikasi ──────────────────────────────────────
//     $booking->load('survey', 'customer');

//     NotificationHelper::bookingCreatedForTukang(
//         $booking,
//         auth()->user()->name
//     );

//     NotificationHelper::bookingConfirmedForCustomer($booking);
//     // ─────────────────────────────────────────────────────────

//     return response()->json([
//         'status'  => true,
//         'message' => 'Booking berhasil dibuat',
//         'data'    => $booking,
//     ]);
// }

// // ─────────────────────────────────────────────────────────────
// // GET /api/customer/orders/{id}/booking-detail
// //
// // Detail order booking beserta info estimasi dari survey
// // ─────────────────────────────────────────────────────────────
// public function getBookingDetail(int $id): JsonResponse
// {
//     $order = Order::with(['orderDetail', 'tukang', 'survey'])
//         ->where('id', $id)
//         ->where('user_id', auth()->id())
//         ->first();

//     if (!$order) {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Order tidak ditemukan',
//         ], 404);
//     }

//     return response()->json([
//         'status' => true,
//         'data'   => [
//             'id'             => $order->id,
//             'survey_id'      => $order->survey_id,
//             'scheduled_date' => $order->scheduled_date,
//             'address'        => $order->address,
//             'notes'          => $order->notes,
//             'total_amount'   => (float) $order->total_amount,
//             'status'         => $order->status,
//             'tukang'         => $order->tukang ? [
//                 'id'   => $order->tukang->id,
//                 'name' => $order->tukang->name,
//             ] : null,
//             'detail' => $order->orderDetail ? [
//                 'material_cost' => (float) $order->orderDetail->material_cost,
//                 'service_cost'  => (float) $order->orderDetail->service_cost,
//                 'total_cost'    => (float) $order->orderDetail->total_cost,
//                 'duration_days' => $order->orderDetail->duration_days,
//                 'notes'         => $order->orderDetail->notes,
//             ] : null,
//             'survey' => $order->survey ? [
//                 'damage_description' => $order->survey->damage_description,
//                 'tukang_notes'       => $order->survey->tukang_notes,
//             ] : null,
//         ],
//     ]);
// }

// // ─────────────────────────────────────────────────────────────
// // USE STATEMENTS tambahan (jika belum ada)
// // ─────────────────────────────────────────────────────────────
// // use Illuminate\Http\JsonResponse;
// // use Illuminate\Http\Request;
// // use Illuminate\Support\Facades\DB;
// // use Illuminate\Support\Facades\Validator;
// // use App\Models\Order;
// // use App\Models\OrderDetail;
// // use App\Models\SurveyRequest;
// // use App\Models\UserNotification;

// }
