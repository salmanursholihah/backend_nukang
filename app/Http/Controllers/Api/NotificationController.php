<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\PaymentOrder;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

// class NotificationController extends Controller
// {
//     public function handle(Request $request)
//     {
//         $payload = $request->all();

//         // Pastikan field wajib ada
//         $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key'];
//         foreach ($requiredFields as $field) {
//             if (empty($payload[$field])) {
//                 return response()->json([
//                     'message' => "Field '$field' tidak ditemukan."
//                 ], 422);
//             }
//         }

//         // 1. Verifikasi signature key
//         $signatureKey = hash(
//             'sha512',
//             $payload['order_id'] .
//                 $payload['status_code'] .
//                 $payload['gross_amount'] .
//                 config('midtrans.server_key')
//         );

//         if ($signatureKey !== $payload['signature_key']) {
//             Log::warning('Midtrans: Invalid signature', [
//                 'order_id' => $payload['order_id']
//             ]);
//             return response()->json(['message' => 'Invalid signature'], 403);
//         }

//         // 2. Cari order
//         $order = PaymentOrder::where('order_id', $payload['order_id'])->first();
//         if (!$order) {
//             return response()->json(['message' => 'Order not found'], 404);
//         }

//         // 3. Update status
//         $transactionStatus = $payload['transaction_status'] ?? 'pending';
//         $fraudStatus       = $payload['fraud_status'] ?? null;

//         if ($transactionStatus === 'capture') {
//             if ($fraudStatus === 'accept') {
//                 $order->update([
//                     'payment_status' => 'settlement',
//                     'paid_at'        => now(),
//                     'transaction_id' => $payload['transaction_id'] ?? null,
//                 ]);
//             } elseif ($fraudStatus === 'challenge') {
//                 $order->update(['payment_status' => 'challenge']);
//             }
//         } elseif ($transactionStatus === 'settlement') {
//             $order->update([
//                 'payment_status' => 'settlement',
//                 'paid_at'        => now(),
//                 'transaction_id' => $payload['transaction_id'] ?? null,
//             ]);
//         } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
//             $order->update(['payment_status' => $transactionStatus]);
//         } elseif ($transactionStatus === 'pending') {
//             $order->update(['payment_status' => 'pending']);
//         }

//         Log::info('Midtrans notification', [
//             'order_id' => $payload['order_id'],
//             'status'   => $transactionStatus,
//         ]);

//         Log::info('MIDTRANS CALLBACK:', $request->all());
//         return response()->json(['status' => 'ok'], 200);


//     }
// }



///code 2

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/customer/notifications
    public function index(Request $request): JsonResponse
    {
        $notifications = UserNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'data'   => collect($notifications->items())->map(
                fn($n) => $this->formatNotif($n)
            ),
            'meta'   => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
                'unread_count' => UserNotification::where('user_id', auth()->id())
                    ->where('is_read', false)
                    ->count(),
            ],
        ]);
    }

    // GET /api/customer/notifications/unread-count
    public function unreadCount(): JsonResponse
    {
        $count = UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => true,
            'data'   => ['unread_count' => $count],
        ]);
    }

    // PUT /api/customer/notifications/{id}/read
    public function markRead(int $id): JsonResponse
    {
        $notif = UserNotification::where('user_id', auth()->id())
            ->findOrFail($id);

        $notif->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Notifikasi ditandai dibaca.']);
    }

    // PUT /api/customer/notifications/read-all
    public function markAllRead(): JsonResponse
    {
        UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['status' => true, 'message' => 'Semua notifikasi ditandai dibaca.']);
    }

    // ── Format response ───────────────────────────────────────
    private function formatNotif(UserNotification $n): array
    {
        return [
            'id'           => $n->id,
            'title'        => $n->title,
            'body'         => $n->body,
            'type'         => $n->type,
            // ✅ KRITIS: kirim notifiable_id agar Flutter bisa fetch detail
            'notifiable_id' => $n->notifiable_id,
            'reference_id'  => $n->notifiable_id, // alias untuk kompatibilitas
            // ✅ KRITIS: kirim field data (berisi survey_id, address, dll)
            'data'         => $n->data ?? [],
            'is_read'      => $n->is_read,
            'read_at'      => $n->read_at?->toIso8601String(),
            'created_at'   => $n->created_at->toIso8601String(),
        ];
    }
}
