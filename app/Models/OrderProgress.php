<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property int                             $order_id
 * @property string                          $title
 * @property string|null                     $description
 * @property int                             $percent
 * @property \Carbon\Carbon|null             $reported_at
 * @property \Carbon\Carbon                  $created_at
 * @property \Carbon\Carbon                  $updated_at
 *
 * @property-read \App\Models\Order                              $order
 * @property-read \Illuminate\Database\Eloquent\Collection       $photos
 */


class OrderProgress extends Model
{
    use HasFactory;
    protected $table = 'order_progresses';

    protected $guarded = [];

    protected $casts = [
        'reported_at' => 'datetime',
        'percent' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function photos()
    {
        return $this->hasMany(OrderProgressPhoto::class);
    }
}

