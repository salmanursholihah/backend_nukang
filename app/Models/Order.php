<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function progresses()
    {
        return $this->hasMany(OrderProgress::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
