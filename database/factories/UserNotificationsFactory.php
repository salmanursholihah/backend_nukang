<?php

namespace Database\Factories;

use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    private array $typeConfig = [
        'general' => [
            'titles' => [
                'Welcome to the platform!',
                'Profile incomplete',
                'Tips for getting started',
                'New feature available',
            ],
            'bodies' => [
                'Thank you for joining us. Explore all the features we have to offer.',
                'Please complete your profile to get the best experience.',
                'Check out our latest tips to make the most of the platform.',
                'We just launched a new feature. Click to learn more.',
            ],
            'notifiable_type' => null,
        ],
        'order' => [
            'titles' => [
                'Your order has been placed',
                'Order is being processed',
                'Order shipped',
                'Order delivered',
                'Order cancelled',
            ],
            'bodies' => [
                'Your order has been successfully placed and is being processed.',
                'We are preparing your items. You will be notified once shipped.',
                'Your order is on its way. Estimated delivery: 2–3 business days.',
                'Your order has been delivered. Enjoy your purchase!',
                'Your order has been cancelled. Refund will be processed shortly.',
            ],
            'notifiable_type' => 'App\\Models\\Order',
        ],
        'payment' => [
            'titles' => [
                'Payment successful',
                'Payment failed',
                'Payment pending',
                'Refund processed',
            ],
            'bodies' => [
                'Your payment has been confirmed successfully.',
                'Your payment attempt was unsuccessful. Please try again.',
                'Your payment is being verified. Please wait.',
                'Your refund has been processed and will arrive in 3–5 business days.',
            ],
            'notifiable_type' => 'App\\Models\\Payment',
        ],
        'comment' => [
            'titles' => [
                'Someone commented on your post',
                'New reply to your comment',
            ],
            'bodies' => [
                'Someone left a comment on your post.',
                'Someone replied to your comment.',
            ],
            'notifiable_type' => 'App\\Models\\Post',
        ],
        'mention' => [
            'titles' => [
                'You were mentioned in a comment',
                'You were mentioned in a post',
            ],
            'bodies' => [
                'Someone mentioned you in a comment.',
                'Someone mentioned you in a post.',
            ],
            'notifiable_type' => 'App\\Models\\Comment',
        ],
        'security' => [
            'titles' => [
                'New login detected',
                'Password changed',
                'Two-factor authentication enabled',
            ],
            'bodies' => [
                'A new login to your account was detected.',
                'Your account password has been changed.',
                'Two-factor authentication has been enabled on your account.',
            ],
            'notifiable_type' => null,
        ],
        'system' => [
            'titles' => [
                'System maintenance scheduled',
                'Service restored',
                'Platform update available',
            ],
            'bodies' => [
                'The platform will be down for scheduled maintenance.',
                'All services have been restored. Thank you for your patience.',
                'A new platform update is available.',
            ],
            'notifiable_type' => null,
        ],
    ];

    public function definition(): array
    {
        $type   = $this->faker->randomKey($this->typeConfig);
        $config = $this->typeConfig[$type];

        $index    = $this->faker->numberBetween(0, count($config['titles']) - 1);
        $isRead   = $this->faker->boolean(40); // 40% chance already read
        $readAt   = $isRead ? $this->faker->dateTimeBetween('-7 days', 'now') : null;
        $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'user_id'         => $this->faker->numberBetween(1, 10),
            'title'           => $config['titles'][$index],
            'body'            => $config['bodies'][$index],
            'type'            => $type,
            'notifiable_type' => $config['notifiable_type'],
            'notifiable_id'   => $config['notifiable_type']
                ? $this->faker->numberBetween(1, 1000)
                : null,
            'data'            => json_encode([
                'action_url' => '/' . $type . 's/' . $this->faker->numberBetween(1, 999),
            ]),
            'is_read'         => $isRead ? 1 : 0,
            'read_at'         => $readAt,
            'created_at'      => $createdAt,
            'updated_at'      => $createdAt,
        ];
    }

    /** Mark notification as read */
    public function read(): static
    {
        return $this->state(fn() => [
            'is_read' => 1,
            'read_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /** Mark notification as unread */
    public function unread(): static
    {
        return $this->state(fn() => [
            'is_read' => 0,
            'read_at' => null,
        ]);
    }

    /** Force a specific type */
    public function ofType(string $type): static
    {
        $config = $this->typeConfig[$type];
        $index  = $this->faker->numberBetween(0, count($config['titles']) - 1);

        return $this->state(fn() => [
            'type'            => $type,
            'title'           => $config['titles'][$index],
            'body'            => $config['bodies'][$index],
            'notifiable_type' => $config['notifiable_type'],
            'notifiable_id'   => $config['notifiable_type']
                ? $this->faker->numberBetween(1, 1000)
                : null,
        ]);
    }

    /** Assign to a specific user */
    public function forUser(int $userId): static
    {
        return $this->state(fn() => ['user_id' => $userId]);
    }
}
