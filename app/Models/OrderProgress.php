<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProgress extends Model
{
    use HasFactory;
    protected $table = 'order_progresses';

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
