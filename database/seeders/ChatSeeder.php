<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $tukangs   = User::where('role', 'tukang')->get();

        $conversations = [
            ['Halo, apakah bisa datang hari ini?',       'Bisa, saya akan datang pukul 10 pagi.'],
            ['Berapa estimasi biaya untuk pasang AC?',    'Untuk pasang AC 1 PK sekitar Rp 350.000.'],
            ['Apakah sudah selesai pengerjaannya?',       'Sudah selesai, silakan dicek.'],
            ['Bisa minta foto progress-nya?',             'Baik, saya kirimkan sekarang.'],
            ['Terima kasih atas pelayanannya!',           'Sama-sama, senang bisa membantu.'],
        ];

        foreach ($conversations as $i => $conv) {
            $customer = $customers->get($i % $customers->count());
            $tukang   = $tukangs->get($i % $tukangs->count());

            $chat = Chat::create([
                'customer_id'     => $customer->id,
                'tukang_id'       => $tukang->id,
                'last_message_at' => now()->subMinutes(rand(5, 120)),
            ]);

            // 5 pesan per chat
            Message::create(['chat_id' => $chat->id, 'sender_id' => $customer->id, 'message' => $conv[0], 'type' => 'text', 'is_read' => true]);
            Message::create(['chat_id' => $chat->id, 'sender_id' => $tukang->id,   'message' => $conv[1], 'type' => 'text', 'is_read' => false]);
        }
    }
}
