<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['last_message_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
