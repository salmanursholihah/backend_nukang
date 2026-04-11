<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukangLocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'is_online'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    // Scope: cari tukang online dalam radius X km
    // Contoh: TukangLocation::nearby(-7.797068, 110.370529, 10)->get()
    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 10)
    {
        return $query
            ->selectRaw("*, ( 6371 * acos(
                    cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude))
                )) AS distance_km", [$lat, $lng, $lat])
            ->where('is_online', true)
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km');
    }
}
