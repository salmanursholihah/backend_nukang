<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Notification;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

// class UserNotificationController extends Controller
// {
//     // =========================================================
//     // INDEX — Daftar notifikasi milik user yang login
//     // GET /api/notifications?page=1
//     // =========================================================
//     public function index(Request $request): JsonResponse
//     {
//         $notifications = Notification::where('user_id', $request->user()->id)
//             ->latest()
//             ->paginate($request->get('per_page', 15));

//         return response()->json([
//             'status'  => true,
//             'message' => 'Berhasil memuat notifikasi.',
//             'meta'    => [
//                 'current_page' => $notifications->currentPage(),
//                 'last_page'    => $notifications->lastPage(),
//                 'total'        => $notifications->total(),
//             ],
//             'data' => $notifications->items(),
//         ]);
//     }

//     // =========================================================
//     // UNREAD COUNT — Jumlah notifikasi belum dibaca
//     // GET /api/notifications/unread-count
//     // =========================================================
//     public function unreadCount(Request $request): JsonResponse
//     {
//         $count = Notification::where('user_id', $request->user()->id)
//             ->where('is_read', false)
//             ->count();

//         return response()->json([
//             'status' => true,
//             'data'   => ['count' => $count],
//         ]);
//     }

//     // =========================================================
//     // MARK READ — Tandai satu notifikasi sudah dibaca
//     // PUT /api/notifications/{id}/read
//     // =========================================================
//     public function markRead(Request $request, int $id): JsonResponse
//     {
//         $notification = Notification::where('user_id', $request->user()->id)
//             ->where('id', $id)
//             ->first();

//         if (! $notification) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Notifikasi tidak ditemukan.',
//             ], 404);
//         }

//         $notification->update([
//             'is_read' => true,
//             'read_at' => now(),
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Notifikasi ditandai sudah dibaca.',
//         ]);
//     }

//     // =========================================================
//     // MARK ALL READ — Tandai semua notifikasi sudah dibaca
//     // PUT /api/notifications/read-all
//     // =========================================================
//     public function markAllRead(Request $request): JsonResponse
//     {
//         Notification::where('user_id', $request->user()->id)
//             ->where('is_read', false)
//             ->update([
//                 'is_read' => true,
//                 'read_at' => now(),
//             ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Semua notifikasi telah ditandai sudah dibaca.',
//         ]);
//     }
// }




// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Notification;
// use App\Models\SurveyRequest;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

// class UserNotificationController extends Controller
// {
//     // =========================================================
//     // INDEX — Daftar notifikasi milik user yang login
//     // GET /api/notifications?page=1
//     // =========================================================
//     public function index(Request $request): JsonResponse
//     {
//         $notifications = Notification::where('user_id', $request->user()->id)
//             ->latest()
//             ->paginate($request->get('per_page', 15));

//         return response()->json([
//             'status'  => true,
//             'message' => 'Berhasil memuat notifikasi.',
//             'meta'    => [
//                 'current_page' => $notifications->currentPage(),
//                 'last_page'    => $notifications->lastPage(),
//                 'total'        => $notifications->total(),
//             ],
//             'data' => $notifications->items(),
//         ]);
//     }

//     // =========================================================
//     // UNREAD COUNT — Jumlah notifikasi belum dibaca
//     // GET /api/notifications/unread-count
//     // =========================================================
//     public function unreadCount(Request $request): JsonResponse
//     {
//         $count = Notification::where('user_id', $request->user()->id)
//             ->where('is_read', false)
//             ->count();

//         return response()->json([
//             'status' => true,
//             'data'   => ['count' => $count],
//         ]);
//     }

//     // =========================================================
//     // MARK READ — Tandai satu notifikasi sudah dibaca
//     // PUT /api/notifications/{id}/read
//     // =========================================================
//     public function markRead(Request $request, int $id): JsonResponse
//     {
//         $notification = Notification::where('user_id', $request->user()->id)
//             ->where('id', $id)
//             ->first();

//         if (! $notification) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Notifikasi tidak ditemukan.',
//             ], 404);
//         }

//         $notification->update([
//             'is_read' => true,
//             'read_at' => now(),
//         ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Notifikasi ditandai sudah dibaca.',
//         ]);
//     }

//     // =========================================================
//     // MARK ALL READ — Tandai semua notifikasi sudah dibaca
//     // PUT /api/notifications/read-all
//     // =========================================================
//     public function markAllRead(Request $request): JsonResponse
//     {
//         Notification::where('user_id', $request->user()->id)
//             ->where('is_read', false)
//             ->update([
//                 'is_read' => true,
//                 'read_at' => now(),
//             ]);

//         return response()->json([
//             'status'  => true,
//             'message' => 'Semua notifikasi telah ditandai sudah dibaca.',
//         ]);
//     }

//     public function approve($id)
//     {
//         $survey = SurveyRequest::findOrFail($id);

//         // 1. Update status
//         $survey->status = 'approved';
//         $survey->save();

//         // 2. 🔥 TAMBAHKAN NOTIFIKASI KE CUSTOMER
//         \App\Models\Notification::create([
//             'user_id' => $survey->user_id, // 👉 customer
//             'title' => 'Survey Disetujui',
//             'message' => 'Pengajuan survey kamu telah disetujui oleh tukang',
//             'type' => 'survey_approved',
//             'reference_id' => $survey->id,
//         ]);

//         // 3. (Optional tapi bagus) buat order setelah approve
//         $order = \App\Models\Order::create([
//             'user_id' => $survey->user_id,
//             'survey_id' => $survey->id,
//             'status' => 'waiting_payment',
//             'total_price' => 50000, // biaya survey
//         ]);

//         // 4. Return sesuai kebutuhan Flutter kamu
//         return response()->json([
//             'success' => true,
//             'message' => 'Survey approved',
//             'data' => [
//                 'survey' => $survey,
//                 'order' => $order,
//             ]
//         ]);
//     }
// }



///code kesekian


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyRequest;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $notifications = UserNotification::where('user_id', $userId)
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
                'unread_count' => UserNotification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count(),
            ],
        ]);
    }

    // GET /api/notifications/unread-count
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

    // PUT /api/notifications/{id}/read
    public function markRead(int $id): JsonResponse
    {
        $notif = UserNotification::where('user_id', auth()->id())
            ->findOrFail($id);

        $notif->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Notifikasi ditandai dibaca.',
        ]);
    }

    // PUT /api/notifications/read-all
    public function markAllRead(): JsonResponse
    {
        UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'status'  => true,
            'message' => 'Semua notifikasi ditandai dibaca.',
        ]);
    }

    // DELETE /api/notifications/{id}
    public function delete(int $id): JsonResponse
    {
        UserNotification::where('user_id', auth()->id())
            ->findOrFail($id)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Notifikasi dihapus.',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // FORMAT — pastikan field 'data' selalu terisi
    // Jika data kosong di DB, fallback load dari survey langsung
    // ─────────────────────────────────────────────────────────
    private function formatNotif(UserNotification $n): array
    {
        $data = $n->data ?? [];

        // ✅ FIX: Jika data kosong tapi notifiable_id ada,
        // coba load data dari survey langsung (untuk notif lama)
        if (
            empty($data) &&
            $n->notifiable_id !== null &&
            in_array($n->type, ['survey_approved', 'survey_priced', 'survey'])
        ) {
            $survey = SurveyRequest::with(['tukang', 'service'])
                ->find($n->notifiable_id);

            if ($survey) {
                $data = [
                    'survey_id'    => $survey->id,
                    'tukang_name'  => $survey->tukang?->name ?? '',
                    'service_name' => $survey->service?->name ?? '',
                    'address'      => $survey->address,
                    'survey_date'  => $survey->survey_date?->toDateTimeString(),
                    'survey_fee'   => (float) ($survey->survey_fee ?? 0),
                    'status'       => $survey->status,
                ];
            }
        }

        return [
            'id'            => $n->id,
            'title'         => $n->title,
            'body'          => $n->body,
            'type'          => $n->type,
            'notifiable_id' => $n->notifiable_id,
            'reference_id'  => $n->notifiable_id,
            'data'          => $data,
            'is_read'       => $n->is_read,
            'read_at'       => $n->read_at?->toIso8601String(),
            'created_at'    => $n->created_at->toIso8601String(),
        ];
    }
}
