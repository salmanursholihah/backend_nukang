<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Service;
use App\Models\TukangProfile;
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
    // Query params:
    //   ?status=pending|accepted|on_progress|completed|cancelled
    //   ?per_page=10
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = Order::with([
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating,is_verified',
            'details.service:id,name,thumbnail',
            'payment:id,order_id,status,method,amount',
        ])
            ->where('customer_id', $request->user()->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
            'data' => collect($orders->items())->map(fn($o) => $this->formatOrder($o)),
        ]);
    }


    // =========================================================
    // STORE — Buat order baru
    // POST /api/customer/orders
    // Body:
    //   tukang_id       : int (required)
    //   address         : string (required)
    //   latitude        : decimal (required)
    //   longitude       : decimal (required)
    //   service_date    : datetime (required)
    //   notes           : string (optional)
    //   items           : array (required)
    //     - service_id  : int
    //     - qty         : int
    // =========================================================

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tukang_id'           => 'required|exists:users,id',
            'address'             => 'required|string',
            'latitude'            => 'required|numeric',
            'longitude'           => 'required|numeric',
            'service_date'        => 'required|date|after:now',
            'notes'               => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.service_id'  => 'required|exists:services,id',
            'items.*.qty'         => 'required|integer|min:1',
        ]);

        // Cek tukang tersedia dan aktif
        $tukangProfile = TukangProfile::where('user_id', $request->tukang_id)
            ->where('is_available', true)
            ->where('is_verified', true)
            ->first();

        if (! $tukangProfile) {
            return response()->json([
                'status'  => false,
                'message' => 'Tukang tidak tersedia saat ini.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Hitung subtotal dari setiap item
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $service = Service::find($item['service_id']);

                if (! $service || ! $service->is_active) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Service '{$service?->name}' tidak tersedia.",
                    ], 422);
                }

                // Cek apakah tukang bisa mengerjakan service ini
                $tukangService = DB::table('tukang_services')
                    ->where('tukang_id', $request->tukang_id)
                    ->where('service_id', $item['service_id'])
                    ->first();

                // Gunakan harga custom tukang jika ada, fallback ke base_price
                $price    = $tukangService?->custom_price ?? $service->base_price ?? 0;
                $itemSubtotal = $price * $item['qty'];
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'price'        => $price,
                    'qty'          => $item['qty'],
                    'subtotal'     => $itemSubtotal,
                ];
            }

            $serviceFee = $subtotal * 0.10; // 10% komisi platform
            $totalPrice = $subtotal + $serviceFee;

            // Buat order
            $order = Order::create([
                'customer_id'   => $request->user()->id,
                'tukang_id'     => $request->tukang_id,
                'address'       => $request->address,
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
                'service_date'  => $request->service_date,
                'notes'         => $request->notes,
                'subtotal'      => $subtotal,
                'service_fee'   => $serviceFee,
                'total_price'   => $totalPrice,
                'status'        => 'pending',
            ]);

            // Buat order details
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
                'message' => 'Gagal membuat order. Silakan coba lagi.',
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
        // Pastikan order milik customer ini
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
    // Body:
    //   cancel_reason : string (required)
    // =========================================================

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Hanya bisa cancel jika masih pending atau accepted
        if (! in_array($order->status, ['pending', 'accepted'])) {
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
    // PROGRESSES — Foto progress pengerjaan
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
    // PAYMENT DETAIL — Cek detail pembayaran order
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
                'id'         => $order->tukang->id,
                'name'       => $order->tukang->name,
                'avatar_url' => $order->tukang->avatar ? asset($order->tukang->avatar) : null,
                'photo_url'  => $order->tukang->tukangProfile?->photo
                    ? asset($order->tukang->tukangProfile->photo) : null,
                'rating'     => $order->tukang->tukangProfile?->rating,
                'is_verified' => $order->tukang->tukangProfile?->is_verified,
            ] : null,
            'items'        => $order->relationLoaded('details')
                ? $order->details->map(fn($d) => [
                    'id'           => $d->id,
                    'service_name' => $d->service_name,
                    'price'        => $d->price,
                    'qty'          => $d->qty,
                    'subtotal'     => $d->subtotal,
                    'thumbnail_url' => $d->service?->thumbnail ? asset($d->service->thumbnail) : null,
                ]) : [],
            'payment_status' => $order->relationLoaded('payment')
                ? $order->payment?->status
                : null,
        ];
    }

    private function formatOrderDetail(Order $order): array
    {
        $data = $this->formatOrder($order);

        // Tambahan data untuk detail
        $data['tukang']['location'] = $order->tukang?->tukangLocation ? [
            'latitude'  => $order->tukang->tukangLocation->latitude,
            'longitude' => $order->tukang->tukangLocation->longitude,
            'is_online' => $order->tukang->tukangLocation->is_online,
        ] : null;

        $data['tukang']['city'] = $order->tukang?->tukangProfile?->city;

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
            'tags'       => $order->review->tags,
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

        $data['cancel_reason']  = $order->cancel_reason;
        $data['started_at']     = $order->started_at?->toDateTimeString();
        $data['completed_at']   = $order->completed_at?->toDateTimeString();

        return $data;
    }
}
