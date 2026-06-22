<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOrder extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'user_id',
        'gross_amount',
        'bank',
        'va_number',
        'payment_status',
        'transaction_id',
        'paid_at',
        'expired_at',
        'midtrans_response',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'paid_at'           => 'datetime',
        'expired_at'        => 'datetime',
        'gross_amount'      => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
