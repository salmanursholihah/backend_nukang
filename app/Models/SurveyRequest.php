<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'survey_fee'      => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'survey_date'     => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function surveyServices()
    {
        return $this->hasMany(SurveyRequestService::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
