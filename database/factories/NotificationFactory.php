<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['order', 'payment', 'chat', 'survey', 'earning', 'system']);

        $titles = [
            'order'   => 'Order Baru Masuk',
            'payment' => 'Pembayaran Berhasil',
            'chat'    => 'Pesan Baru',
            'survey'  => 'Permintaan Survey',
            'earning' => 'Pendapatan Siap Dicairkan',
            'system'  => 'Informasi Sistem',
        ];

        $bodies = [
            'order'   => 'Ada order baru yang menunggu konfirmasi kamu.',
            'payment' => 'Pembayaran untuk ordermu telah dikonfirmasi.',
            'chat'    => 'Kamu mendapat pesan baru dari pelanggan.',
            'survey'  => 'Ada permintaan survey baru dari pelanggan.',
            'earning' => 'Pendapatanmu siap untuk dicairkan.',
            'system'  => 'Ada pembaruan sistem yang perlu kamu ketahui.',
        ];

        $isRead = fake()->boolean(50);

        return [
            'user_id'          => User::factory(),
            'title'            => $titles[$type],
            'body'             => $bodies[$type],
            'type'             => $type,
            'notifiable_id'    => null,
            'notifiable_type'  => null,
            'is_read'          => $isRead,
            'read_at'          => $isRead ? fake()->dateTimeBetween('-7 days', 'now') : null,
        ];
    }

    public function unread(): static
    {
        return $this->state(['is_read' => false, 'read_at' => null]);
    }

    public function read(): static
    {
        return $this->state(['is_read' => true, 'read_at' => now()]);
    }
}
