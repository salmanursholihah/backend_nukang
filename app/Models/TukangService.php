<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukangService extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = ['custom_price' => 'decimal:2'];

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
