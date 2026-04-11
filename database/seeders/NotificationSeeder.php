<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->get();

        $templates = [
            ['title' => 'Order Baru Masuk',           'body' => 'Ada order baru yang menunggu konfirmasi.',      'type' => 'order'],
            ['title' => 'Pembayaran Berhasil',         'body' => 'Pembayaran ordermu telah dikonfirmasi.',        'type' => 'payment'],
            ['title' => 'Pesan Baru',                  'body' => 'Kamu mendapat pesan baru dari pelanggan.',      'type' => 'chat'],
            ['title' => 'Survey Diterima',             'body' => 'Tukang menerima permintaan surveymu.',          'type' => 'survey'],
            ['title' => 'Pendapatan Siap Dicairkan',   'body' => 'Pendapatanmu sudah bisa dicairkan.',            'type' => 'earning'],
        ];

        foreach ($users as $user) {
            foreach ($templates as $i => $tpl) {
                Notification::create([
                    'user_id'   => $user->id,
                    'title'     => $tpl['title'],
                    'body'      => $tpl['body'],
                    'type'      => $tpl['type'],
                    'is_read'   => $i < 2, // 2 pertama sudah dibaca
                    'read_at'   => $i < 2 ? now()->subHour() : null,
                ]);
            }
        }
    }
}
