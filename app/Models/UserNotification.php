<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserNotification extends Model
{
    use HasFactory;

    protected $table   = 'user_notifications';
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    // ── Scope ─────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Helper statis ─────────────────────────────────────────

    public static function send(
        int    $userId,
        string $title,
        string $body,
        string $type,
        Model  $notifiable,
        array  $data = [],
    ): UserNotification {
        // 1. Simpan ke DB — data disimpan as-is (JSON via cast 'array')
        $notif = UserNotification::create([
            'user_id'         => $userId,
            'title'           => $title,
            'body'            => $body,
            'type'            => $type,
            'notifiable_id'   => $notifiable->getKey(),
            'notifiable_type' => get_class($notifiable),
            'data'            => $data,
            'is_read'         => false,
        ]);

        // 2. Kirim FCM jika user punya token
        $user = User::find($userId);
        if ($user && $user->fcm_token) {
            UserNotification::sendFcm(
                token: $user->fcm_token,
                title: $title,
                body: $body,
                type: $type,
                notifId: $notif->id,
                refId: $notifiable->getKey(),
                extra: $data,
            );
        }

        return $notif;
    }

    /**
     * Kirim FCM push notification.
     *
     * ✅ FIX KRITIS: Hapus array_map('strval', $extra) yang merusak semua data.
     *
     * FCM legacy API mensyaratkan semua nilai di 'data' payload berupa string,
     * TAPI untuk nilai kompleks (array nested seperti survey_services),
     * kita encode ke JSON string terlebih dahulu secara selektif.
     *
     * Flutter FcmService sudah handle decode JSON string ini.
     */
    private static function sendFcm(
        string $token,
        string $title,
        string $body,
        string $type,
        int    $notifId,
        mixed  $refId,
        array  $extra = [],
    ): void {
        $serverKey = config('services.fcm.server_key');
        if (! $serverKey) {
            Log::warning('FCM server_key belum dikonfigurasi di services.fcm');
            return;
        }

        // ✅ FIX: Konversi nilai ke string dengan benar per tipe data
        // - scalar (int, float, bool, string) → (string) langsung
        // - array/object → json_encode (bukan 'Array' literal)
        // - null → '' (string kosong)
        $fcmData = [];
        foreach ($extra as $key => $value) {
            if (is_array($value) || is_object($value)) {
                // nested array seperti survey_services → JSON string
                $fcmData[$key] = json_encode($value);
            } elseif (is_null($value)) {
                $fcmData[$key] = '';
            } else {
                // int, float, bool, string → string
                $fcmData[$key] = (string) $value;
            }
        }

        $payload = [
            'to'           => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
            ],
            // ✅ data payload: semua string, nested array sudah di-encode JSON
            'data' => array_merge([
                'type'            => $type,
                'notification_id' => (string) $notifId,
                'notifiable_id'   => (string) $refId,
            ], $fcmData),
        ];

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM send error: ' . $e->getMessage(), [
                'user_token' => substr($token, 0, 10) . '...',
                'type'       => $type,
            ]);
        }
    }
}
