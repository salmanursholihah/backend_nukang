<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // =========================================================
    // INDEX — Daftar notifikasi user
    // GET /api/notifications
    // Query params:
    //   ?type=order|payment|chat|survey|earning|system
    //   ?is_read=0|1
    //   ?per_page=15
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $query = Notification::where('user_id', $request->user()->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', (bool) $request->is_read);
        }

        $notifications = $query->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'unread_count' => Notification::where('user_id', $request->user()->id)
                    ->where('is_read', false)
                    ->count(),
            ],
            'data' => collect($notifications->items())
                ->map(fn($n) => $this->formatNotification($n)),
        ]);
    }


    // =========================================================
    // UNREAD COUNT — Jumlah notifikasi belum dibaca
    // GET /api/notifications/unread-count
    // =========================================================

    public function unreadCount(Request $request): JsonResponse
    {
        $counts = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $totalUnread = array_sum($counts);

        return response()->json([
            'status' => true,
            'data'   => [
                'total'   => $totalUnread,
                'by_type' => [
                    'order'   => $counts['order']   ?? 0,
                    'payment' => $counts['payment'] ?? 0,
                    'chat'    => $counts['chat']    ?? 0,
                    'survey'  => $counts['survey']  ?? 0,
                    'earning' => $counts['earning'] ?? 0,
                    'system'  => $counts['system']  ?? 0,
                ],
            ],
        ]);
    }


    // =========================================================
    // MARK READ — Tandai satu notifikasi sudah dibaca
    // PUT /api/notifications/{id}/read
    // =========================================================

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->find($id);

        if (! $notification) {
            return response()->json([
                'status'  => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'status'  => true,
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data'    => $this->formatNotification($notification),
        ]);
    }


    // =========================================================
    // MARK ALL READ — Tandai semua notifikasi sudah dibaca
    // PUT /api/notifications/read-all
    // =========================================================

    public function markAllRead(Request $request): JsonResponse
    {
        $updated = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
            'data'    => [
                'updated_count' => $updated,
            ],
        ]);
    }


    // =========================================================
    // HELPERS
    // =========================================================

    private function formatNotification(Notification $notification): array
    {
        return [
            'id'         => $notification->id,
            'title'      => $notification->title,
            'body'       => $notification->body,
            'type'       => $notification->type,
            'is_read'    => $notification->is_read,
            'read_at'    => $notification->read_at?->toDateTimeString(),
            'created_at' => $notification->created_at->diffForHumans(),
            // Data untuk deep link di Flutter
            'notifiable' => [
                'type' => $notification->notifiable_type
                    ? class_basename($notification->notifiable_type)
                    : null,
                'id'   => $notification->notifiable_id,
            ],
        ];
    }
}
