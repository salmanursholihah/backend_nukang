<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $chats = Chat::with(['customer', 'tukang', 'messages'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $chats,
        ]);
    }

    public function startChat(Request $request)
    {
        $data = $request->validate([
            'tukang_id' => ['required', 'exists:users,id'],
        ]);

        $chat = Chat::firstOrCreate([
            'customer_id' => $request->user()->id,
            'tukang_id' => $data['tukang_id'],
        ]);

        return response()->json([
            'message' => 'Chat berhasil dibuat',
            'data' => $chat,
        ]);
    }

    public function messages(Request $request, $chatId)
    {
        $chat = Chat::where('customer_id', $request->user()->id)
            ->findOrFail($chatId);

        $messages = Message::with('sender')
            ->where('chat_id', $chat->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $chatId)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $chat = Chat::where('customer_id', $request->user()->id)
            ->findOrFail($chatId);

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $request->user()->id,
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Pesan berhasil dikirim',
            'data' => $message->load('sender'),
        ], 201);
    }
}
