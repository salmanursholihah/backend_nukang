<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukangProfile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'rating'       => 'decimal:2',
        'radius_km'    => 'decimal:2',
        'is_verified'  => 'boolean',
        'is_available' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}