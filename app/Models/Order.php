<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property int                             $customer_id
 * @property int|null                        $tukang_id
 * @property string                          $order_number
 * @property string                          $status
 * @property string|null                     $address
 * @property float|null                      $latitude
 * @property float|null                      $longitude
 * @property float                           $subtotal
 * @property float                           $service_fee
 * @property float                           $total_price
 * @property string|null                     $notes
 * @property string|null                     $cancel_reason
 * @property \Carbon\Carbon|null             $service_date
 * @property \Carbon\Carbon|null             $started_at
 * @property \Carbon\Carbon|null             $completed_at
 * @property \Carbon\Carbon                  $created_at
 * @property \Carbon\Carbon                  $updated_at
 *
 * @property-read \App\Models\User|null                          $customer
 * @property-read \App\Models\User|null                          $tukang
 * @property-read \Illuminate\Database\Eloquent\Collection       $details
 * @property-read \Illuminate\Database\Eloquent\Collection       $progresses
 * @property-read \App\Models\Payment|null                       $payment
 * @property-read \App\Models\Review|null                        $review
 */


class Order extends Model
{


    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'service_date' => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Auto-generate order_number
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number = 'NKG-' . now()->format('Ymd') . '-' . str_pad(
                Order::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
        });
    }


    public function surveyRequest()
    {
        return $this->belongsTo(SurveyRequest::class);
    }

    public function earning()
    {
        return $this->hasOne(PartnerEarning::class);
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
    public function scopeActive($q)
    {
        return $q->whereIn('status', ['accepted', 'on_progress']);
    }
    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function progresses()
    {
        return $this->hasMany(OrderProgress::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}


