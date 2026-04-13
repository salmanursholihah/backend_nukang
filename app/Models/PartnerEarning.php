<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerEarning extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'amount'       => 'decimal:2',
        'settled_at'   => 'datetime',
    ];

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
