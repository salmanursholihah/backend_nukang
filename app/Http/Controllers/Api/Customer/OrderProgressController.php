<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OrderProgressResource;
use App\Models\Order;
use App\Models\OrderProgress;
use App\Models\OrderProgressPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrderProgressController extends Controller
{
    // ────────────────────────────────────────────────────────
    // [CUSTOMER] GET /api/customer/orders/{orderId}/progress
    // ────────────────────────────────────────────────────────
    public function indexForCustomer(Request $request, int $orderId): JsonResponse
    {
        $order = Order::where('id', $orderId)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $progressList = OrderProgress::where('order_id', $orderId)
            ->with('photos')
            ->orderBy('percent')
            ->orderBy('reported_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => OrderProgressResource::collection($progressList),
            'meta'   => [
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'order_status'   => $order->status,
                'latest_percent' => $progressList->max('percent') ?? 0,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────
    // [TUKANG] GET /api/tukang/orders/{orderId}/progress
    // ────────────────────────────────────────────────────────
    public function indexForTukang(Request $request, int $orderId): JsonResponse
    {
        $order = Order::where('id', $orderId)
            ->where('tukang_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $progressList = OrderProgress::where('order_id', $orderId)
            ->with('photos')
            ->orderBy('percent')
            ->orderBy('reported_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => OrderProgressResource::collection($progressList),
            'meta'   => [
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'order_status'   => $order->status,
                'latest_percent' => $progressList->max('percent') ?? 0,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────
    // [TUKANG] POST /api/tukang/orders/{orderId}/progress
    // Body (multipart/form-data):
    //   title        : string, required
    //   description  : string, required
    //   percent      : integer 0-100, required
    //   photos[]     : file image, optional, max 5
    // ────────────────────────────────────────────────────────
    public function store(Request $request, int $orderId): JsonResponse
    {
        $order = Order::where('id', $orderId)
            ->where('tukang_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if (!in_array($order->status, ['accepted', 'on_progress'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Progress hanya bisa ditambah pada order yang sedang berjalan.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'percent'     => 'required|integer|min:0|max:100',
            'photos'      => 'nullable|array|max:5',
            'photos.*'    => 'image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $progress = OrderProgress::create([
                'order_id'    => $orderId,
                'title'       => $request->title,
                'description' => $request->description,
                'percent'     => $request->percent,
                'reported_at' => now(),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $foto) {
                    $path = $foto->store("order_progress/{$orderId}", 'public');
                    $url  = Storage::disk('public')->url($path);

                    OrderProgressPhoto::create([
                        'order_progress_id' => $progress->id,
                        'photo_path'        => $path,
                        'photo_url'         => $url,
                    ]);
                }
            }

            if ($order->status === 'accepted') {
                $order->update(['status' => 'on_progress']);
            }

            DB::commit();
            $progress->load('photos');

            return response()->json([
                'status'  => true,
                'message' => 'Progress berhasil ditambahkan.',
                'data'    => new OrderProgressResource($progress),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // [TUKANG] DELETE /api/tukang/orders/{orderId}/progress/{progressId}
    // ────────────────────────────────────────────────────────
    public function destroy(Request $request, int $orderId, int $progressId): JsonResponse
    {
        $progress = OrderProgress::whereHas('order', function ($q) use ($orderId, $request) {
            $q->where('id', $orderId)
                ->where('tukang_id', $request->user()->id);
        })->find($progressId);

        if (!$progress) {
            return response()->json([
                'status'  => false,
                'message' => 'Progress tidak ditemukan.',
            ], 404);
        }

        foreach ($progress->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $progress->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Progress berhasil dihapus.',
        ]);
    }
}
