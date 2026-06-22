<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $users = User::pluck('id')->toArray();

        // minimal 2 user
        if (count($users) < 2) {
            $this->command->warn('User kurang dari 2, seeder dilewati');
            return;
        }

        $conversations = [];

        // generate pasangan user unik
        for ($i = 0; $i < count($users); $i++) {
            for ($j = $i + 1; $j < count($users); $j++) {
                $userOne = $users[$i];
                $userTwo = $users[$j];

                $conversations[] = [
                    'user_one_id' => $userOne,
                    'user_two_id' => $userTwo,
                    'last_message_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('chat_conversations')->insert($conversations);

        $this->command->info('Chat conversations seeded: ' . count($conversations));
    }

}
