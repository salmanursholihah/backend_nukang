<?php

namespace App\Http\Controllers\Api\Tukang;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProgress;
use App\Models\OrderProgressPhoto;
use App\Models\PartnerEarning;
use App\Models\UserNotification;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'progresses.photos',
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
            return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
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
            return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Order hanya bisa ditolak jika masih berstatus pending.',
            ], 422);
        }

        $request->validate(['cancel_reason' => 'required|string|max:255']);

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
            return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
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
    //
    // FIX: status check diubah dari 'in_progress' → 'on_progress'
    //      agar konsisten dengan status yang di-set oleh start()
    // =========================================================
    public function complete(Request $request, Order $order): JsonResponse
    {
        if ($order->tukang_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        // ✅ FIX: was 'in_progress' — harus 'on_progress' sesuai start()
        if ($order->status !== 'on_progress') {
            return response()->json([
                'status'  => false,
                'message' => 'Order harus berstatus on_progress untuk diselesaikan. Status saat ini: ' . $order->status,
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Update order → completed
            $order->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            // 2. Update PartnerEarning → settled (siap dicairkan)
            $earning = PartnerEarning::where('order_id', $order->id)->first();

            if ($earning && $earning->status === 'pending') {
                $earning->update([
                    'status'     => 'settled',
                    'settled_at' => now(),
                ]);

                Log::info('[complete] Earning settled for order_id: ' . $order->id, [
                    'earning_id' => $earning->id,
                    'amount'     => $earning->amount,
                ]);
            } elseif (!$earning) {
                // Safety net: earning belum dibuat (edge case payment offline/cash)
                $platformFeeRate = 0.10;
                $orderAmount     = (float) $order->total_price;
                $platformFee     = round($orderAmount * $platformFeeRate, 2);
                $tukangAmount    = round($orderAmount - $platformFee, 2);

                $earning = PartnerEarning::create([
                    'tukang_id'    => $order->tukang_id,
                    'order_id'     => $order->id,
                    'order_amount' => $orderAmount,
                    'platform_fee' => $platformFee,
                    'amount'       => $tukangAmount,
                    'status'       => 'settled',
                    'settled_at'   => now(),
                ]);

                Log::info('[complete] Earning created & settled (safety net) for order_id: ' . $order->id);
            }

            // 3. Notifikasi ke customer
            UserNotification::send(
                userId: $order->customer_id,
                title: 'Pekerjaan Selesai',
                body: 'Tukang telah menyelesaikan pekerjaan. Silakan berikan ulasan.',
                type: 'order_completed',
                notifiable: $order,
                data: [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                ],
            );

            DB::commit();

            // Refresh earning setelah commit
            $earning->refresh();

            return response()->json([
                'status'  => true,
                'message' => 'Order berhasil diselesaikan.',
                'data'    => [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'status'       => $order->status,
                    'completed_at' => $order->completed_at?->toDateTimeString(),
                    'earning'      => [
                        'id'         => $earning->id,
                        'amount'     => $earning->amount,
                        'status'     => $earning->status,
                        'settled_at' => $earning->settled_at?->toDateTimeString(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[complete] Error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyelesaikan order.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // ADD PROGRESS — Tambah tahap progress + multi foto
    // POST /api/tukang/orders/{order}/progress
    //
    // Body (multipart/form-data):
    //   title       : string (required)
    //   description : string (optional)
    //   percent     : integer 0–100 (required)
    //   photos[]    : image jpg/jpeg/png/webp, max 5 file, max 3MB each
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
            'percent'     => 'required|integer|min:0|max:100',
            'photos'      => 'nullable|array|max:5',
            'photos.*'    => 'image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        DB::beginTransaction();
        try {
            $progress = OrderProgress::create([
                'order_id'    => $order->id,
                'title'       => $request->title,
                'description' => $request->description,
                'percent'     => $request->percent,
                'reported_at' => now(),
            ]);

            $photoData = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $foto) {
                    $path = $this->uploadImage($foto, 'orders');
                    $url  = asset($path);

                    $photo = OrderProgressPhoto::create([
                        'order_progress_id' => $progress->id,
                        'photo_path'        => $path,
                        'photo_url'         => $url,
                    ]);

                    $photoData[] = [
                        'id'        => $photo->id,
                        'photo_url' => $url,
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Progress berhasil ditambahkan.',
                'data'    => [
                    'id'          => $progress->id,
                    'title'       => $progress->title,
                    'description' => $progress->description,
                    'percent'     => $progress->percent,
                    'reported_at' => $progress->reported_at?->toDateTimeString(),
                    'photos'      => $photoData,
                    'created_at'  => $progress->created_at->toDateTimeString(),
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[addProgress] Error: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // DELETE PROGRESS — Hapus tahap progress beserta fotonya
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

        foreach ($progress->photos as $photo) {
            $this->deleteImage($photo->photo_path);
        }

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

        $data['customer']['phone'] = $order->customer?->phone;

        $data['progresses'] = $order->relationLoaded('progresses')
            ? $order->progresses->map(fn($p) => [
                'id'          => $p->id,
                'title'       => $p->title,
                'description' => $p->description,
            'percent'     => $p->percent,
            'reported_at' => $p->reported_at?->toDateTimeString(),
            'photos'      => $p->relationLoaded('photos')
                ? $p->photos->map(fn($ph) => [
                    'id'        => $ph->id,
                    'photo_url' => $ph->photo_url,
                ]) : [],
                'created_at'  => $p->created_at->toDateTimeString(),
            ]) : [];

        $data['payment'] = $order->relationLoaded('payment') && $order->payment ? [
            'status'  => $order->payment->status,
            'method'  => $order->payment->method,
            'paid_at' => $order->payment->paid_at?->toDateTimeString(),
        ] : null;

        $data['review'] = $order->relationLoaded('review') && $order->review ? [
            'rating'  => $order->review->rating,
            'comment' => $order->review->comment,
            'tags'    => $order->review->tags,
        ] : null;

        return $data;
    }
}
///code sebelumnya part 2
// namespace App\Http\Controllers\Api\Tukang;
// use App\Http\Controllers\Controller;
// use App\Models\Order;
// use App\Models\OrderProgress;
// use App\Models\OrderProgressPhoto;
// use App\Traits\ImageUploadTrait;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;

// class JobOrderController extends Controller
// {
//     use ImageUploadTrait;

//     // =========================================================
//     // INDEX — Daftar order yang masuk ke tukang
//     // GET /api/tukang/orders
//     // =========================================================
//     public function index(Request $request): JsonResponse
//     {
//         $query = Order::with([
//                 'customer:id,name,avatar',
//                 'details.service:id,name,thumbnail',
//                 'payment:id,order_id,status,method',
//             ])
//             ->where('tukang_id', $request->user()->id);

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
//                 'summary'      => $this->getStatusSummary($request->user()->id),
//             ],
//             'data' => collect($orders->items())->map(fn($o) => $this->formatOrder($o)),
//         ]);
//     }

//     // =========================================================
//     // SHOW — Detail order
//     // GET /api/tukang/orders/{order}
//     // =========================================================
//     public function show(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         $order->load([
//             'customer:id,name,avatar,phone',
//             'details.service:id,name,thumbnail,unit',
//             'progresses.photos',   // ← load foto per progress
//             'payment:id,order_id,status,method,paid_at',
//             'review:id,order_id,rating,comment,tags',
//         ]);

//         return response()->json([
//             'status' => true,
//             'data'   => $this->formatOrderDetail($order),
//         ]);
//     }

//     // =========================================================
//     // ACCEPT
//     // =========================================================
//     public function accept(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
//         }

//         if ($order->status !== 'pending') {
//             return response()->json(['status' => false, 'message' => 'Order hanya bisa diterima jika masih berstatus pending.'], 422);
//         }

//         $order->update(['status' => 'accepted']);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Order berhasil diterima.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }

//     // =========================================================
//     // REJECT
//     // =========================================================
//     public function reject(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
//         }

//         if ($order->status !== 'pending') {
//             return response()->json(['status' => false, 'message' => 'Order hanya bisa ditolak jika masih berstatus pending.'], 422);
//         }

//         $request->validate(['cancel_reason' => 'required|string|max:255']);

//         $order->update([
//             'status'        => 'cancelled',
//             'cancel_reason' => $request->cancel_reason,
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Order ditolak.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }

//     // =========================================================
//     // START
//     // =========================================================
//     public function start(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
//         }

//         if ($order->status !== 'accepted') {
//             return response()->json(['status' => false, 'message' => 'Order harus berstatus accepted sebelum bisa dimulai.'], 422);
//         }

//         $order->update([
//             'status'     => 'on_progress',
//             'started_at' => now(),
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Pengerjaan dimulai.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }

//     // =========================================================
//     // COMPLETE
//     // =========================================================
//     // public function complete(Request $request, Order $order): JsonResponse
//     // {
//     //     if ($order->tukang_id !== $request->user()->id) {
//     //         return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
//     //     }

//     //     if ($order->status !== 'on_progress') {
//     //         return response()->json(['status' => false, 'message' => 'Order harus berstatus on_progress untuk diselesaikan.'], 422);
//     //     }

//     //     $order->update([
//     //         'status'       => 'completed',
//     //         'completed_at' => now(),
//     //     ]);

//     //     return response()->json([
//     //         'status'  => true,
//     //         'message' => 'Order selesai. Pendapatan akan segera diproses.',
//     //         'data'    => $this->formatOrder($order->fresh()),
//     //     ]);
//     // }

//     public function complete(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json(['status' => false, 'message' => 'Order tidak ditemukan.'], 404);
//         }

//         if ($order->status !== 'in_progress') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak bisa diselesaikan. Status saat ini: ' . $order->status,
//             ], 422);
//         }

//         DB::beginTransaction();
//         try {
//             // 1. Update order → completed
//             $order->update([
//             'status'       => 'completed',
//             'completed_at' => now(),
//         ]);

//             // 2. Update PartnerEarning → settled (siap dicairkan)
//             $earning = PartnerEarning::where('order_id', $order->id)->first();

//             if ($earning && $earning->status === 'pending') {
//                 $earning->update([
//                     'status'     => 'settled',
//                     'settled_at' => now(),
//                 ]);

//                 Log::info('[complete] Earning settled for order_id: ' . $order->id, [
//                     'earning_id' => $earning->id,
//                     'amount'     => $earning->amount,
//                 ]);
//             } elseif (!$earning) {
//                 // ✅ Safety net: earning belum dibuat (edge case payment offline/cash)
//                 $platformFeeRate = 0.10;
//                 $orderAmount     = (float) $order->total_price;
//                 $platformFee     = round($orderAmount * $platformFeeRate, 2);
//                 $tukangAmount    = round($orderAmount - $platformFee, 2);

//                 PartnerEarning::create([
//                     'tukang_id'    => $order->tukang_id,
//                     'order_id'     => $order->id,
//                     'order_amount' => $orderAmount,
//                     'platform_fee' => $platformFee,
//                     'amount'       => $tukangAmount,
//                     'status'       => 'settled',   // langsung settled karena order sudah selesai
//                     'settled_at'   => now(),
//                 ]);

//                 Log::info('[complete] Earning created & settled (safety net) for order_id: ' . $order->id);
//             }

//             // 3. Notifikasi ke customer
//             UserNotification::send(
//                 userId: $order->customer_id,
//                 title: 'Pekerjaan Selesai',
//                 body: 'Tukang telah menyelesaikan pekerjaan. Silakan berikan ulasan.',
//                 type: 'order_completed',
//                 notifiable: $order,
//                 data: [
//                     'order_id'     => $order->id,
//                     'order_number' => $order->order_number,
//                 ],
//             );

//             DB::commit();

//             return response()->json([
//             'status'  => true,
//                 'message' => 'Order berhasil diselesaikan.',
//                 'data'    => [
//                     'order_id'     => $order->id,
//                     'order_number' => $order->order_number,
//                     'status'       => $order->status,
//                     'completed_at' => $order->completed_at?->toDateTimeString(),
//                     'earning'      => $earning ? [
//                         'id'         => $earning->id,
//                         'amount'     => $earning->amount,
//                         'status'     => $earning->fresh()->status,
//                         'settled_at' => $earning->fresh()->settled_at?->toDateTimeString(),
//                     ] : null,
//                 ],
//         ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             Log::error('[complete] Error: ' . $e->getMessage());
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Gagal menyelesaikan order.',
//                 'error'   => $e->getMessage(),
//             ], 500);
//         }
//     }


//     // =========================================================
//     // ADD PROGRESS — Tambah tahap progress + multi foto
//     // POST /api/tukang/orders/{order}/progress
//     //
//     // Body (multipart/form-data):
//     //   title       : string (required)
//     //   description : string (optional)
//     //   percent     : integer 0–100 (required)
//     //   photos[]    : image jpg/jpeg/png/webp, max 5 file, max 3MB each
//     // =========================================================
//     public function addProgress(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($order->status !== 'on_progress') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Progress hanya bisa ditambahkan saat order sedang dikerjakan.',
//             ], 422);
//         }

//         $request->validate([
//             'title'       => 'required|string|max:255',
//             'description' => 'nullable|string',
//             'percent'     => 'required|integer|min:0|max:100',
//             'photos'      => 'nullable|array|max:5',
//             'photos.*'    => 'image|mimes:jpg,jpeg,png,webp|max:3072',
//         ]);

//         DB::beginTransaction();
//         try {
//             // Buat progress
//             $progress = OrderProgress::create([
//                 'order_id'    => $order->id,
//                 'title'       => $request->title,
//                 'description' => $request->description,
//                 'percent'     => $request->percent,
//                 'reported_at' => now(),
//             ]);

//             // Upload multi foto jika ada
//             $photoData = [];
//             if ($request->hasFile('photos')) {
//                 foreach ($request->file('photos') as $foto) {
//                     // Pakai ImageUploadTrait yang sudah ada
//                     $path = $this->uploadImage($foto, 'orders');
//                     $url  = asset($path);

//                     $photo = OrderProgressPhoto::create([
//                         'order_progress_id' => $progress->id,
//                         'photo_path'        => $path,
//                         'photo_url'         => $url,
//                     ]);

//                     $photoData[] = [
//                         'id'        => $photo->id,
//                         'photo_url' => $url,
//                     ];
//                 }
//             }

//             DB::commit();

//             return response()->json([
//                 'status'  => true,
//                 'message' => 'Progress berhasil ditambahkan.',
//                 'data'    => [
//                     'id'          => $progress->id,
//                     'title'       => $progress->title,
//                     'description' => $progress->description,
//                     'percent'     => $progress->percent,
//                     'reported_at' => $progress->reported_at?->toDateTimeString(),
//                     'photos'      => $photoData,
//                     'created_at'  => $progress->created_at->toDateTimeString(),
//                 ],
//             ], 201);
//         } catch (\Throwable $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

//     // =========================================================
//     // DELETE PROGRESS — Hapus tahap progress beserta fotonya
//     // DELETE /api/tukang/orders/{order}/progress/{progress}
//     // =========================================================
//     public function deleteProgress(Request $request, Order $order, OrderProgress $progress): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($progress->order_id !== $order->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Progress tidak ditemukan.',
//             ], 404);
//         }

//         // Hapus semua foto dari storage
//         foreach ($progress->photos as $photo) {
//             $this->deleteImage($photo->photo_path);
//         }

//         $progress->delete();

//         return response()->json([
//             'status'  => true,
//             'message' => 'Progress berhasil dihapus.',
//         ]);
//     }

//     // =========================================================
//     // PRIVATE HELPERS
//     // =========================================================
//     private function getStatusSummary(int $tukangId): array
//     {
//         $counts = Order::where('tukang_id', $tukangId)
//             ->selectRaw('status, count(*) as total')
//             ->groupBy('status')
//             ->pluck('total', 'status')
//             ->toArray();

//         return [
//             'pending'     => $counts['pending']     ?? 0,
//             'accepted'    => $counts['accepted']    ?? 0,
//             'on_progress' => $counts['on_progress'] ?? 0,
//             'completed'   => $counts['completed']   ?? 0,
//             'cancelled'   => $counts['cancelled']   ?? 0,
//         ];
//     }

//     private function formatOrder(Order $order): array
//     {
//         return [
//             'id'             => $order->id,
//             'order_number'   => $order->order_number,
//             'status'         => $order->status,
//             'service_date'   => $order->service_date?->toDateTimeString(),
//             'address'        => $order->address,
//             'latitude'       => $order->latitude,
//             'longitude'      => $order->longitude,
//             'subtotal'       => $order->subtotal,
//             'service_fee'    => $order->service_fee,
//             'total_price'    => $order->total_price,
//             'notes'          => $order->notes,
//             'cancel_reason'  => $order->cancel_reason,
//             'started_at'     => $order->started_at?->toDateTimeString(),
//             'completed_at'   => $order->completed_at?->toDateTimeString(),
//             'created_at'     => $order->created_at->toDateTimeString(),
//             'customer'       => $order->relationLoaded('customer') ? [
//                 'id'         => $order->customer->id,
//                 'name'       => $order->customer->name,
//                 'avatar_url' => $order->customer->avatar
//                     ? asset($order->customer->avatar) : null,
//             ] : null,
//             'items'          => $order->relationLoaded('details')
//                 ? $order->details->map(fn($d) => [
//                     'id'            => $d->id,
//                     'service_name'  => $d->service_name,
//                     'price'         => $d->price,
//                     'qty'           => $d->qty,
//                     'subtotal'      => $d->subtotal,
//                     'thumbnail_url' => $d->service?->thumbnail
//                         ? asset($d->service->thumbnail) : null,
//                 ]) : [],
//             'payment_status' => $order->relationLoaded('payment')
//                 ? $order->payment?->status : null,
//         ];
//     }

//     private function formatOrderDetail(Order $order): array
//     {
//         $data = $this->formatOrder($order);

//         $data['customer']['phone'] = $order->customer?->phone;

//         // Progress sekarang include foto
//         $data['progresses'] = $order->relationLoaded('progresses')
//             ? $order->progresses->map(fn($p) => [
//                 'id'          => $p->id,
//                 'title'       => $p->title,
//                 'description' => $p->description,
//             'percent'     => $p->percent,
//             'reported_at' => $p->reported_at?->toDateTimeString(),
//             'photos'      => $p->relationLoaded('photos')
//                 ? $p->photos->map(fn($ph) => [
//                     'id'        => $ph->id,
//                     'photo_url' => $ph->photo_url,
//                 ]) : [],
//                 'created_at'  => $p->created_at->toDateTimeString(),
//             ]) : [];

//         $data['payment'] = $order->relationLoaded('payment') && $order->payment ? [
//             'status'  => $order->payment->status,
//             'method'  => $order->payment->method,
//             'paid_at' => $order->payment->paid_at?->toDateTimeString(),
//         ] : null;

//         $data['review'] = $order->relationLoaded('review') && $order->review ? [
//             'rating'  => $order->review->rating,
//             'comment' => $order->review->comment,
//             'tags'    => $order->review->tags,
//         ] : null;

//         return $data;
//     }
// }


///code sebelumnya
// use App\Http\Controllers\Controller;
// use App\Models\Order;
// use App\Models\OrderProgress;
// use App\Traits\ImageUploadTrait;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

// class JobOrderController extends Controller
// {
//       use ImageUploadTrait;

//     // =========================================================
//     // INDEX — Daftar order yang masuk ke tukang
//     // GET /api/tukang/orders
//     // Query params:
//     //   ?status=pending|accepted|on_progress|completed|cancelled
//     //   ?per_page=10
//     // =========================================================

//     public function index(Request $request): JsonResponse
//     {
//         $query = Order::with([
//                 'customer:id,name,avatar',
//                 'details.service:id,name,thumbnail',
//                 'payment:id,order_id,status,method',
//             ])
//             ->where('tukang_id', $request->user()->id);

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
//                 // Ringkasan jumlah per status
//                 'summary'      => $this->getStatusSummary($request->user()->id),
//             ],
//             'data' => collect($orders->items())->map(fn($o) => $this->formatOrder($o)),
//         ]);
//     }


//     // =========================================================
//     // SHOW — Detail order
//     // GET /api/tukang/orders/{order}
//     // =========================================================

//     public function show(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         $order->load([
//             'customer:id,name,avatar,phone',
//             'details.service:id,name,thumbnail,unit',
//             'progresses',
//             'payment:id,order_id,status,method,paid_at',
//             'review:id,order_id,rating,comment,tags',
//         ]);

//         return response()->json([
//             'status' => true,
//             'data'   => $this->formatOrderDetail($order),
//         ]);
//     }


//     // =========================================================
//     // ACCEPT — Terima order
//     // PUT /api/tukang/orders/{order}/accept
//     // =========================================================

//     public function accept(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($order->status !== 'pending') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order hanya bisa diterima jika masih berstatus pending.',
//             ], 422);
//         }

//         $order->update(['status' => 'accepted']);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Order berhasil diterima.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }


//     // =========================================================
//     // REJECT — Tolak order
//     // PUT /api/tukang/orders/{order}/reject
//     // Body:
//     //   cancel_reason : string (required)
//     // =========================================================

//     public function reject(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($order->status !== 'pending') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order hanya bisa ditolak jika masih berstatus pending.',
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
//             'message' => 'Order ditolak.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }


//     // =========================================================
//     // START — Mulai pengerjaan
//     // PUT /api/tukang/orders/{order}/start
//     // =========================================================

//     public function start(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($order->status !== 'accepted') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order harus berstatus accepted sebelum bisa dimulai.',
//             ], 422);
//         }

//         $order->update([
//             'status'     => 'on_progress',
//             'started_at' => now(),
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Pengerjaan dimulai.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }


//     // =========================================================
//     // COMPLETE — Selesaikan order
//     // PUT /api/tukang/orders/{order}/complete
//     // =========================================================

//     public function complete(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($order->status !== 'on_progress') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order harus berstatus on_progress untuk diselesaikan.',
//             ], 422);
//         }

//         $order->update([
//             'status'       => 'completed',
//             'completed_at' => now(),
//         ]);

//         // Observer akan otomatis:
//         // - increment total_jobs di tukang_profiles
//         // - buat partner_earnings

//         return response()->json([
//             'status'  => true,
//             'message' => 'Order selesai. Pendapatan akan segera diproses.',
//             'data'    => $this->formatOrder($order->fresh()),
//         ]);
//     }


//     // =========================================================
//     // ADD PROGRESS — Tambah foto progress pengerjaan
//     // POST /api/tukang/orders/{order}/progress
//     // Body (multipart):
//     //   title       : string (required)
//     //   description : string (optional)
//     //   photo       : image (optional)
//     // =========================================================

//     public function addProgress(Request $request, Order $order): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($order->status !== 'on_progress') {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Progress hanya bisa ditambahkan saat order sedang dikerjakan.',
//             ], 422);
//         }

//         $request->validate([
//             'title'       => 'required|string|max:255',
//             'description' => 'nullable|string',
//             'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
//         ]);

//         $photoPath = null;
//         if ($request->hasFile('photo')) {
//             // Simpan ke public/images/orders/
//             $photoPath = $this->uploadImage($request->file('photo'), 'orders');
//         }

//         $progress = OrderProgress::create([
//             'order_id'    => $order->id,
//             'title'       => $request->title,
//             'description' => $request->description,
//             'photo'       => $photoPath,
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Progress berhasil ditambahkan.',
//             'data'    => [
//                 'id'          => $progress->id,
//                 'title'       => $progress->title,
//                 'description' => $progress->description,
//                 'photo_url'   => $progress->photo ? asset($progress->photo) : null,
//                 'created_at'  => $progress->created_at->toDateTimeString(),
//             ],
//         ], 201);
//     }


//     // =========================================================
//     // DELETE PROGRESS — Hapus foto progress
//     // DELETE /api/tukang/orders/{order}/progress/{progress}
//     // =========================================================

//     public function deleteProgress(Request $request, Order $order, OrderProgress $progress): JsonResponse
//     {
//         if ($order->tukang_id !== $request->user()->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Order tidak ditemukan.',
//             ], 404);
//         }

//         if ($progress->order_id !== $order->id) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Progress tidak ditemukan.',
//             ], 404);
//         }

//         // Hapus foto dari public/images/orders/
//         $this->deleteImage($progress->photo);
//         $progress->delete();

//         return response()->json([
//             'status'  => true,
//             'message' => 'Progress berhasil dihapus.',
//         ]);
//     }


//     // =========================================================
//     // PRIVATE HELPERS
//     // =========================================================

//     private function getStatusSummary(int $tukangId): array
//     {
//         $counts = Order::where('tukang_id', $tukangId)
//             ->selectRaw('status, count(*) as total')
//             ->groupBy('status')
//             ->pluck('total', 'status')
//             ->toArray();

//         return [
//             'pending'     => $counts['pending']     ?? 0,
//             'accepted'    => $counts['accepted']    ?? 0,
//             'on_progress' => $counts['on_progress'] ?? 0,
//             'completed'   => $counts['completed']   ?? 0,
//             'cancelled'   => $counts['cancelled']   ?? 0,
//         ];
//     }

//     private function formatOrder(Order $order): array
//     {
//         return [
//             'id'             => $order->id,
//             'order_number'   => $order->order_number,
//             'status'         => $order->status,
//             'service_date'   => $order->service_date?->toDateTimeString(),
//             'address'        => $order->address,
//             'latitude'       => $order->latitude,
//             'longitude'      => $order->longitude,
//             'subtotal'       => $order->subtotal,
//             'service_fee'    => $order->service_fee,
//             'total_price'    => $order->total_price,
//             'notes'          => $order->notes,
//             'cancel_reason'  => $order->cancel_reason,
//             'started_at'     => $order->started_at?->toDateTimeString(),
//             'completed_at'   => $order->completed_at?->toDateTimeString(),
//             'created_at'     => $order->created_at->toDateTimeString(),
//             'customer'       => $order->relationLoaded('customer') ? [
//                 'id'         => $order->customer->id,
//                 'name'       => $order->customer->name,
//                 'avatar_url' => $order->customer->avatar
//                     ? asset($order->customer->avatar) : null,
//             ] : null,
//             'items'          => $order->relationLoaded('details')
//                 ? $order->details->map(fn($d) => [
//                     'id'            => $d->id,
//                     'service_name'  => $d->service_name,
//                     'price'         => $d->price,
//                     'qty'           => $d->qty,
//                     'subtotal'      => $d->subtotal,
//                     'thumbnail_url' => $d->service?->thumbnail
//                         ? asset($d->service->thumbnail) : null,
//                 ]) : [],
//             'payment_status' => $order->relationLoaded('payment')
//                 ? $order->payment?->status : null,
//         ];
//     }

//     private function formatOrderDetail(Order $order): array
//     {
//         $data = $this->formatOrder($order);

//         // Tambahan untuk detail
//         $data['customer']['phone'] = $order->customer?->phone;

//         $data['progresses'] = $order->relationLoaded('progresses')
//             ? $order->progresses->map(fn($p) => [
//                 'id'          => $p->id,
//                 'title'       => $p->title,
//                 'description' => $p->description,
//                 'photo_url'   => $p->photo ? asset($p->photo) : null,
//                 'created_at'  => $p->created_at->toDateTimeString(),
//             ]) : [];

//         $data['payment'] = $order->relationLoaded('payment') && $order->payment ? [
//             'status'   => $order->payment->status,
//             'method'   => $order->payment->method,
//             'paid_at'  => $order->payment->paid_at?->toDateTimeString(),
//         ] : null;

//         $data['review'] = $order->relationLoaded('review') && $order->review ? [
//             'rating'  => $order->review->rating,
//             'comment' => $order->review->comment,
//             'tags'    => $order->review->tags,
//         ] : null;

//         return $data;
//     }
// }
