<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    use ImageUploadTrait;

    // =========================================================
    // INDEX — Ambil pesan dalam chat (dengan pagination)
    // GET /api/chats/{chat}/messages
    // Query params:
    //   ?per_page=20
    //   ?before_id=100  → load lebih lama dari message id tertentu (infinite scroll)
    // =========================================================

    public function index(Request $request, Chat $chat): JsonResponse
    {
        $userId = $request->user()->id;

        if ($chat->customer_id !== $userId && $chat->tukang_id !== $userId) {
            return response()->json([
                'status'  => false,
                'message' => 'Chat tidak ditemukan.',
            ], 404);
        }

        $query = Message::where('chat_id', $chat->id);

        // Infinite scroll — load pesan lebih lama
        if ($request->filled('before_id')) {
            $query->where('id', '<', $request->before_id);
        }

        $messages = $query->latest()
            ->paginate($request->get('per_page', 20));

        // Tandai pesan dari lawan sebagai sudah dibaca
        Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'meta'   => [
                'total'        => $messages->total(),
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'has_more'     => $messages->hasMorePages(),
            ],
            // Urutkan dari lama ke baru untuk ditampilkan di UI
            'data' => collect($messages->items())
                ->reverse()
                ->values()
                ->map(fn($m) => $this->formatMessage($m, $userId)),
        ]);
    }


    // =========================================================
    // STORE — Kirim pesan
    // POST /api/chats/{chat}/messages
    // Body (multipart):
    //   message    : string (required jika tidak ada attachment)
    //   attachment : image/file (optional)
    //   type       : text|image|file (default: text)
    // =========================================================

    public function store(Request $request, Chat $chat): JsonResponse
    {
        $userId = $request->user()->id;

        if ($chat->customer_id !== $userId && $chat->tukang_id !== $userId) {
            return response()->json([
                'status'  => false,
                'message' => 'Chat tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'message'    => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'type'       => 'sometimes|in:text,image,file',
        ]);

        // Minimal harus ada message atau attachment
        if (! $request->filled('message') && ! $request->hasFile('attachment')) {
            return response()->json([
                'status'  => false,
                'message' => 'Pesan atau attachment harus diisi.',
            ], 422);
        }

        $attachmentPath = null;
        $messageType    = $request->input('type', 'text');

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();

            // Tentukan folder dan type berdasarkan mime
            if (str_starts_with($mime, 'image/')) {
                $attachmentPath = $this->uploadImage($file, 'chats');
                $messageType    = 'image';
            } else {
                // File non-image simpan ke public/images/chats/files/
                $directory = public_path('images/chats/files');
                if (! \Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }
                $filename       = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($directory, $filename);
                $attachmentPath = "images/chats/files/{$filename}";
                $messageType    = 'file';
            }
        }

        $message = Message::create([
            'chat_id'    => $chat->id,
            'sender_id'  => $userId,
            'message'    => $request->input('message'),
            'attachment' => $attachmentPath,
            'type'       => $messageType,
            'is_read'    => false,
        ]);

        // Update last_message_at di chat
        $chat->update(['last_message_at' => now()]);

        return response()->json([
            'status'  => true,
            'message' => 'Pesan berhasil dikirim.',
            'data'    => $this->formatMessage($message, $userId),
        ], 201);
    }


    // =========================================================
    // MARK READ — Tandai semua pesan sudah dibaca
    // PUT /api/chats/{chat}/messages/read
    // =========================================================

    public function markRead(Request $request, Chat $chat): JsonResponse
    {
        $userId = $request->user()->id;

        if ($chat->customer_id !== $userId && $chat->tukang_id !== $userId) {
            return response()->json([
                'status'  => false,
                'message' => 'Chat tidak ditemukan.',
            ], 404);
        }

        $updated = Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status'  => true,
            'message' => 'Pesan berhasil ditandai sudah dibaca.',
            'data'    => [
                'updated_count' => $updated,
            ],
        ]);
    }


    // =========================================================
    // HELPERS
    // =========================================================

    private function formatMessage(Message $message, int $userId): array
    {
        return [
            'id'              => $message->id,
            'chat_id'         => $message->chat_id,
            'sender_id'       => $message->sender_id,
            'is_mine'         => $message->sender_id === $userId,
            'message'         => $message->message,
            'attachment_url'  => $message->attachment ? asset($message->attachment) : null,
            'type'            => $message->type,
            'is_read'         => $message->is_read,
            'created_at'      => $message->created_at->toDateTimeString(),
        ];
    }
}
