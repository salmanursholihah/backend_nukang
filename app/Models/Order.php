<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    public function surveyRequest()
    {
        return $this->belongsTo(SurveyRequest::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function progresses()
    {
        return $this->hasMany(OrderProgress::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
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
}
