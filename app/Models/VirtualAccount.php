<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// FIX: hapus import Attribute yang tidak digunakan

class VirtualAccount extends Model
{
    protected $fillable = [
        'order_id',
        'va_number',
        'customer_name',
        'customer_email',
        'amount',
        'status',
        'paid_at',
        'expired_at',
        'bca_response',
        'callback_payload',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'paid_at'          => 'datetime',
        'expired_at'       => 'datetime',
        'bca_response'     => 'array',
        'callback_payload' => 'array',
    ];

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED' ||
            ($this->expired_at && $this->expired_at->isPast());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }
}
