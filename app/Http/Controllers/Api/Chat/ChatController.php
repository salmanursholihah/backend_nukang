<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // =========================================================
    // INDEX — Daftar chat user (sebagai customer atau tukang)
    // GET /api/chats
    // =========================================================

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $chats = Chat::with([
            'customer:id,name,avatar',
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,is_online',
            'tukang.tukangLocation:tukang_id,is_online',
            'messages' => fn($q) => $q->latest()->limit(1),
        ])
            ->where('customer_id', $userId)
            ->orWhere('tukang_id', $userId)
            ->latest('last_message_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $chats->map(fn($c) => $this->formatChat($c, $userId)),
        ]);
    }


    // =========================================================
    // STORE — Mulai chat baru
    // POST /api/chats
    // Body:
    //   tukang_id  : int (required jika user adalah customer)
    //   customer_id: int (required jika user adalah tukang)
    //   order_id   : int (optional) link ke order
    // =========================================================

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isCustomer()) {
            $request->validate([
                'tukang_id' => 'required|exists:users,id',
                'order_id'  => 'nullable|exists:orders,id',
            ]);

            $customerId = $user->id;
            $tukangId   = $request->tukang_id;
        } else {
            $request->validate([
                'customer_id' => 'required|exists:users,id',
                'order_id'    => 'nullable|exists:orders,id',
            ]);

            $customerId = $request->customer_id;
            $tukangId   = $user->id;
        }

        // Cek apakah chat sudah ada
        $chat = Chat::where('customer_id', $customerId)
            ->where('tukang_id', $tukangId)
            ->first();

        // Jika sudah ada → return chat yang ada
        if ($chat) {
            $chat->load([
                'customer:id,name,avatar',
                'tukang:id,name,avatar',
                'tukang.tukangProfile:user_id,photo',
                'tukang.tukangLocation:tukang_id,is_online',
                'messages' => fn($q) => $q->latest()->limit(1),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Chat sudah ada.',
                'data'    => $this->formatChat($chat, $user->id),
            ]);
        }

        // Buat chat baru
        $chat = Chat::create([
            'customer_id' => $customerId,
            'tukang_id'   => $tukangId,
            'order_id'    => $request->input('order_id'),
        ]);

        $chat->load([
            'customer:id,name,avatar',
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo',
            'tukang.tukangLocation:tukang_id,is_online',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Chat berhasil dibuat.',
            'data'    => $this->formatChat($chat, $user->id),
        ], 201);
    }


    // =========================================================
    // SHOW — Detail chat + info lawan bicara
    // GET /api/chats/{chat}
    // =========================================================

    public function show(Request $request, Chat $chat): JsonResponse
    {
        $userId = $request->user()->id;

        // Pastikan user adalah bagian dari chat ini
        if ($chat->customer_id !== $userId && $chat->tukang_id !== $userId) {
            return response()->json([
                'status'  => false,
                'message' => 'Chat tidak ditemukan.',
            ], 404);
        }

        $chat->load([
            'customer:id,name,avatar',
            'tukang:id,name,avatar',
            'tukang.tukangProfile:user_id,photo,rating,is_verified,city',
            'tukang.tukangLocation:tukang_id,is_online,last_seen_at',
            'order:id,order_number,status',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $this->formatChat($chat, $userId, true),
        ]);
    }


    // =========================================================
    // HELPERS
    // =========================================================

    private function formatChat(Chat $chat, int $userId, bool $detail = false): array
    {
        $isTukang    = $chat->tukang_id === $userId;
        $counterpart = $isTukang ? $chat->customer : $chat->tukang;

        // Pesan terakhir
        $lastMessage = $chat->relationLoaded('messages')
            ? $chat->messages->first()
            : null;

        // Hitung unread milik user ini
        $unreadCount = $chat->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();

        $data = [
            'id'           => $chat->id,
            'order_id'     => $chat->order_id,
            'unread_count' => $unreadCount,
            'last_message' => $lastMessage ? [
                'message'    => $lastMessage->message,
                'type'       => $lastMessage->type,
                'is_read'    => $lastMessage->is_read,
                'created_at' => $lastMessage->created_at->toDateTimeString(),
            ] : null,
            'last_message_at' => $chat->last_message_at?->toDateTimeString(),
            'counterpart'  => $counterpart ? [
                'id'         => $counterpart->id,
                'name'       => $counterpart->name,
                'avatar_url' => $counterpart->avatar ? asset($counterpart->avatar) : null,
            ] : null,
        ];

        // Tambahan jika detail
        if ($detail) {
            if (! $isTukang && $chat->tukang) {
                $data['counterpart']['photo_url']   = $chat->tukang->tukangProfile?->photo
                    ? asset($chat->tukang->tukangProfile->photo) : null;
                $data['counterpart']['city']        = $chat->tukang->tukangProfile?->city;
                $data['counterpart']['rating']      = $chat->tukang->tukangProfile?->rating;
                $data['counterpart']['is_verified'] = $chat->tukang->tukangProfile?->is_verified;
                $data['counterpart']['is_online']   = $chat->tukang->tukangLocation?->is_online ?? false;
                $data['counterpart']['last_seen_at'] = $chat->tukang->tukangLocation?->last_seen_at
                    ?->toDateTimeString();
            }

            $data['order'] = $chat->relationLoaded('order') && $chat->order ? [
                'id'           => $chat->order->id,
                'order_number' => $chat->order->order_number,
                'status'       => $chat->order->status,
            ] : null;
        }

        return $data;
    }
}
