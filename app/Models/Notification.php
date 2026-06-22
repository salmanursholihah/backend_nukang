<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $title
 * @property string                          $body
 * @property string                          $type
 * @property int|null                        $notifiable_id
 * @property int|null                        $reference_id
 * @property array|null                      $data
 * @property bool                            $is_read
 * @property \Carbon\Carbon|null             $read_at
 * @property \Carbon\Carbon                  $created_at
 * @property \Carbon\Carbon                  $updated_at
 */

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data'    => 'array',
    ];

    protected $appends = ['is_read'];

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    // ── Helper static untuk buat notifikasi ───────────────
    public static function send(
        int $userId,
        string $title,
        string $body,
        string $type = 'system',
        ?Model $notifiable = null,
    ): self {
        return self::create([
            'user_id'         => $userId,
            'title'           => $title,
            'body'            => $body,
            'type'            => $type,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id'   => $notifiable?->id,
        ]);
    }

}
