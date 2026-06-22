<?php

use App\Models\Chat;
use App\Models\ChatConversation;
use App\Models\ChatConversations;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Cek apakah user adalah participant chat ini
    return Chat::where('id', $chatId)
        ->where(function ($q) use ($user) {
            $q->where('customer_id', $user->id)
                ->orWhere('tukang_id', $user->id);
        })->exists();
});


Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    return ChatConversation::where('id', $conversationId)
        ->where(function ($q) use ($user) {
            $q->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id);
        })
        ->exists();
});
