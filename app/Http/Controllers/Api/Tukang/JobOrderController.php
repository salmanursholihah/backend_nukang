<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProgress;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobOrderController extends Controller
{
      use ImageUploadTrait;
 
    // =========================================================
    // INDEX — Daftar order yang masuk ke tukang
    // GET /api/tukang/orders
    // Query params:
    //   ?status=pending|accepted|on_progress|completed|cancelled
    //   ?per_page=10
    // =========================================================
 
    public function index(Request $request): JsonResponse
    {
        $query = Order::with([
                'customer:id,name,avatar',
                'details.service:id,name,thumbnail',
                'payment:id,order_id,status,method',
            ])
            ->where('tukang_id', $request->user()->id);
 
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
                // Ringkasan jumlah per status
                'summary'      => $this->getStatusSummary($request->user()->id),
            ],
            'data' => collect($orders->items())->map(fn($o) => $this->formatOrder($o)),
        ]);
    }
 
 
    // =========================================================
    // SHOW — Detail order
    // GET /api/tukang/orders/{order}
    // =========================================================
 
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        $order->load([
            'customer:id,name,avatar,phone',
            'details.service:id,name,thumbnail,unit',
            'progresses',
            'payment:id,order_id,status,method,paid_at',
            'review:id,order_id,rating,comment,tags',
        ]);
 
        return response()->json([
            'status' => true,
            'data'   => $this->formatOrderDetail($order),
        ]);
    }
 
 
    // =========================================================
    // ACCEPT — Terima order
    // PUT /api/tukang/orders/{order}/accept
    // =========================================================
 
    public function accept(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Order hanya bisa diterima jika masih berstatus pending.',
            ], 422);
        }
 
        $order->update(['status' => 'accepted']);
 
        return response()->json([
            'status'  => true,
            'message' => 'Order berhasil diterima.',
            'data'    => $this->formatOrder($order->fresh()),
        ]);
    }
 
 
    // =========================================================
    // REJECT — Tolak order
    // PUT /api/tukang/orders/{order}/reject
    // Body:
    //   cancel_reason : string (required)
    // =========================================================
 
    public function reject(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Order hanya bisa ditolak jika masih berstatus pending.',
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
            'message' => 'Order ditolak.',
            'data'    => $this->formatOrder($order->fresh()),
        ]);
    }
 
 
    // =========================================================
    // START — Mulai pengerjaan
    // PUT /api/tukang/orders/{order}/start
    // =========================================================
 
    public function start(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        if ($order->status !== 'accepted') {
            return response()->json([
                'status'  => false,
                'message' => 'Order harus berstatus accepted sebelum bisa dimulai.',
            ], 422);
        }
 
        $order->update([
            'status'     => 'on_progress',
            'started_at' => now(),
        ]);
 
        return response()->json([
            'status'  => true,
            'message' => 'Pengerjaan dimulai.',
            'data'    => $this->formatOrder($order->fresh()),
        ]);
    }
 
 
    // =========================================================
    // COMPLETE — Selesaikan order
    // PUT /api/tukang/orders/{order}/complete
    // =========================================================
 
    public function complete(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        if ($order->status !== 'on_progress') {
            return response()->json([
                'status'  => false,
                'message' => 'Order harus berstatus on_progress untuk diselesaikan.',
            ], 422);
        }
 
        $order->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
 
        // Observer akan otomatis:
        // - increment total_jobs di tukang_profiles
        // - buat partner_earnings
 
        return response()->json([
            'status'  => true,
            'message' => 'Order selesai. Pendapatan akan segera diproses.',
            'data'    => $this->formatOrder($order->fresh()),
        ]);
    }
 
 
    // =========================================================
    // ADD PROGRESS — Tambah foto progress pengerjaan
    // POST /api/tukang/orders/{order}/progress
    // Body (multipart):
    //   title       : string (required)
    //   description : string (optional)
    //   photo       : image (optional)
    // =========================================================
 
    public function addProgress(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        if ($order->status !== 'on_progress') {
            return response()->json([
                'status'  => false,
                'message' => 'Progress hanya bisa ditambahkan saat order sedang dikerjakan.',
            ], 422);
        }
 
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);
 
        $photoPath = null;
        if ($request->hasFile('photo')) {
            // Simpan ke public/images/orders/
            $photoPath = $this->uploadImage($request->file('photo'), 'orders');
        }
 
        $progress = OrderProgress::create([
            'order_id'    => $order->id,
            'title'       => $request->title,
            'description' => $request->description,
            'photo'       => $photoPath,
        ]);
 
        return response()->json([
            'status'  => true,
            'message' => 'Progress berhasil ditambahkan.',
            'data'    => [
                'id'          => $progress->id,
                'title'       => $progress->title,
                'description' => $progress->description,
                'photo_url'   => $progress->photo ? asset($progress->photo) : null,
                'created_at'  => $progress->created_at->toDateTimeString(),
            ],
        ], 201);
    }
 
 
    // =========================================================
    // DELETE PROGRESS — Hapus foto progress
    // DELETE /api/tukang/orders/{order}/progress/{progress}
    // =========================================================
 
    public function deleteProgress(Request $request, Order $order, OrderProgress $progress): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
 
        if ($progress->order_id !== $order->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Progress tidak ditemukan.',
            ], 404);
        }
 
        // Hapus foto dari public/images/orders/
        $this->deleteImage($progress->photo);
        $progress->delete();
 
        return response()->json([
            'status'  => true,
            'message' => 'Progress berhasil dihapus.',
        ]);
    }
 
 
    // =========================================================
    // PRIVATE HELPERS
    // =========================================================
 
    private function getStatusSummary(int $tukangId): array
    {
        $counts = Order::where('tukang_id', $tukangId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
 
        return [
            'pending'     => $counts['pending']     ?? 0,
            'accepted'    => $counts['accepted']    ?? 0,
            'on_progress' => $counts['on_progress'] ?? 0,
            'completed'   => $counts['completed']   ?? 0,
            'cancelled'   => $counts['cancelled']   ?? 0,
        ];
    }
 
    private function formatOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'status'         => $order->status,
            'service_date'   => $order->service_date?->toDateTimeString(),
            'address'        => $order->address,
            'latitude'       => $order->latitude,
            'longitude'      => $order->longitude,
            'subtotal'       => $order->subtotal,
            'service_fee'    => $order->service_fee,
            'total_price'    => $order->total_price,
            'notes'          => $order->notes,
            'cancel_reason'  => $order->cancel_reason,
            'started_at'     => $order->started_at?->toDateTimeString(),
            'completed_at'   => $order->completed_at?->toDateTimeString(),
            'created_at'     => $order->created_at->toDateTimeString(),
            'customer'       => $order->relationLoaded('customer') ? [
                'id'         => $order->customer->id,
                'name'       => $order->customer->name,
                'avatar_url' => $order->customer->avatar
                    ? asset($order->customer->avatar) : null,
            ] : null,
            'items'          => $order->relationLoaded('details')
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
                ? $order->payment?->status : null,
        ];
    }
 
    private function formatOrderDetail(Order $order): array
    {
        $data = $this->formatOrder($order);
 
        // Tambahan untuk detail
        $data['customer']['phone'] = $order->customer?->phone;
 
        $data['progresses'] = $order->relationLoaded('progresses')
            ? $order->progresses->map(fn($p) => [
                'id'          => $p->id,
                'title'       => $p->title,
                'description' => $p->description,
                'photo_url'   => $p->photo ? asset($p->photo) : null,
                'created_at'  => $p->created_at->toDateTimeString(),
            ]) : [];
 
        $data['payment'] = $order->relationLoaded('payment') && $order->payment ? [
            'status'   => $order->payment->status,
            'method'   => $order->payment->method,
            'paid_at'  => $order->payment->paid_at?->toDateTimeString(),
        ] : null;
 
        $data['review'] = $order->relationLoaded('review') && $order->review ? [
            'rating'  => $order->review->rating,
            'comment' => $order->review->comment,
            'tags'    => $order->review->tags,
        ] : null;
 
        return $data;
    }
}
