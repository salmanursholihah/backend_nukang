<?php

namespace Database\Seeders;

use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserNotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $fixed = [
            // --- general ---
            [
                'user_id'         => 1,
                'title'           => 'Welcome to the platform!',
                'body'            => 'Thank you for joining us. Explore all the features we have to offer.',
                'type'            => 'general',
                'notifiable_type' => null,
                'notifiable_id'   => null,
                'data'            => json_encode(['action_url' => '/dashboard']),
                'is_read'         => 0,
                'read_at'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'user_id'         => 1,
                'title'           => 'Profile incomplete',
                'body'            => 'Please complete your profile to get the best experience.',
                'type'            => 'general',
                'notifiable_type' => null,
                'notifiable_id'   => null,
                'data'            => json_encode(['action_url' => '/profile/edit']),
                'is_read'         => 1,
                'read_at'         => $now->copy()->subHours(2),
                'created_at'      => $now->copy()->subDay(),
                'updated_at'      => $now->copy()->subDay(),
            ],

            // --- order ---
            [
                'user_id'         => 2,
                'title'           => 'Your order has been placed',
                'body'            => 'Order #10234 has been successfully placed and is being processed.',
                'type'            => 'order',
                'notifiable_type' => 'App\\Models\\Order',
                'notifiable_id'   => 10234,
                'data'            => json_encode([
                    'order_id'   => 10234,
                    'amount'     => 150000,
                    'action_url' => '/orders/10234',
                ]),
                'is_read'         => 0,
                'read_at'         => null,
                'created_at'      => $now->copy()->subHours(3),
                'updated_at'      => $now->copy()->subHours(3),
            ],
            [
                'user_id'         => 2,
                'title'           => 'Order shipped',
                'body'            => 'Your order #10234 is on its way. Estimated delivery: 2-3 business days.',
                'type'            => 'order',
                'notifiable_type' => 'App\\Models\\Order',
                'notifiable_id'   => 10234,
                'data'            => json_encode([
                    'order_id'      => 10234,
                    'tracking_code' => 'TRK-ABC123',
                    'action_url'    => '/orders/10234/tracking',
                ]),
                'is_read'         => 1,
                'read_at'         => $now->copy()->subHour(),
                'created_at'      => $now->copy()->subHours(1),
                'updated_at'      => $now->copy()->subHours(1),
            ],

            // --- payment ---
            [
                'user_id'         => 3,
                'title'           => 'Payment successful',
                'body'            => 'Your payment of Rp 250.000 has been confirmed.',
                'type'            => 'payment',
                'notifiable_type' => 'App\\Models\\Payment',
                'notifiable_id'   => 501,
                'data'            => json_encode([
                    'payment_id' => 501,
                    'amount'     => 250000,
                    'method'     => 'bank_transfer',
                    'action_url' => '/payments/501',
                ]),
                'is_read'         => 0,
                'read_at'         => null,
                'created_at'      => $now->copy()->subMinutes(30),
                'updated_at'      => $now->copy()->subMinutes(30),
            ],
            [
                'user_id'         => 3,
                'title'           => 'Payment failed',
                'body'            => 'Your payment attempt was unsuccessful. Please try again.',
                'type'            => 'payment',
                'notifiable_type' => 'App\\Models\\Payment',
                'notifiable_id'   => 502,
                'data'            => json_encode([
                    'payment_id' => 502,
                    'amount'     => 100000,
                    'reason'     => 'insufficient_funds',
                    'action_url' => '/payments/retry/502',
                ]),
                'is_read'         => 0,
                'read_at'         => null,
                'created_at'      => $now->copy()->subMinutes(15),
                'updated_at'      => $now->copy()->subMinutes(15),
            ],

            // --- comment / mention ---
            [
                'user_id'         => 4,
                'title'           => 'Someone commented on your post',
                'body'            => 'Budi commented on your post.',
                'type'            => 'comment',
                'notifiable_type' => 'App\\Models\\Post',
                'notifiable_id'   => 88,
                'data'            => json_encode([
                    'post_id'    => 88,
                    'comment_id' => 320,
                    'commenter'  => 'Budi Santoso',
                    'action_url' => '/posts/88#comment-320',
                ]),
                'is_read'         => 0,
                'read_at'         => null,
                'created_at'      => $now->copy()->subMinutes(5),
                'updated_at'      => $now->copy()->subMinutes(5),
            ],
            [
                'user_id'         => 4,
                'title'           => 'You were mentioned in a comment',
                'body'            => 'Sari mentioned you in a comment.',
                'type'            => 'mention',
                'notifiable_type' => 'App\\Models\\Comment',
                'notifiable_id'   => 321,
                'data'            => json_encode([
                    'post_id'    => 88,
                    'comment_id' => 321,
                    'mentioner'  => 'Sari Dewi',
                    'action_url' => '/posts/88#comment-321',
                ]),
                'is_read'         => 1,
                'read_at'         => $now->copy()->subMinutes(1),
                'created_at'      => $now->copy()->subMinutes(2),
                'updated_at'      => $now->copy()->subMinutes(2),
            ],

            // --- security / system ---
            [
                'user_id'         => 5,
                'title'           => 'System maintenance scheduled',
                'body'            => 'The platform will be down for maintenance on Sunday, 12 May 2026 from 02:00-04:00 WIB.',
                'type'            => 'system',
                'notifiable_type' => null,
                'notifiable_id'   => null,
                'data'            => json_encode([
                    'maintenance_start' => '2026-05-12 02:00:00',
                    'maintenance_end'   => '2026-05-12 04:00:00',
                ]),
                'is_read'         => 0,
                'read_at'         => null,
                'created_at'      => $now->copy()->subDays(2),
                'updated_at'      => $now->copy()->subDays(2),
            ],
            [
                'user_id'         => 5,
                'title'           => 'New login detected',
                'body'            => 'A new login to your account was detected from Jakarta, Indonesia.',
                'type'            => 'security',
                'notifiable_type' => null,
                'notifiable_id'   => null,
                'data'            => json_encode([
                    'ip'         => '103.21.44.0',
                    'location'   => 'Jakarta, Indonesia',
                    'device'     => 'Chrome on Windows',
                    'action_url' => '/account/security',
                ]),
                'is_read'         => 1,
                'read_at'         => $now->copy()->subDay(),
                'created_at'      => $now->copy()->subDays(1),
                'updated_at'      => $now->copy()->subDays(1),
            ],
        ];

        DB::table('user_notifications')->insert($fixed);

        $this->command->info('Inserted ' . count($fixed) . ' fixed user_notifications records.');

        // Uncomment below for bulk random data using Factory:
        // UserNotification::factory(50)->create();
        // UserNotification::factory(20)->forUser(1)->unread()->create();
        // UserNotification::factory(10)->ofType('order')->create();
    }
}
