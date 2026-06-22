<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property int|null $order_id
 * @property int|null $survey_id
 * @property string $type
 * @property int $customer_id
 * @property float $amount
 * @property string $method
 * @property string|null $payment_channel
 * @property string|null $reference_id
 * @property string|null $midtrans_order_id
 * @property string|null $snap_token
 * @property string $status
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon $created_at
 * @property \Illuminate\Database\Eloquent\Relations\BelongsTo $order
 */
class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'amount'           => 'decimal:2',
        'payment_response' => 'array',
        'paid_at'          => 'datetime',
        'expiry_time' => 'datetime',
        'midtrans_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
