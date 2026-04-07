<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'base_price'     => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function surveyRequests()
    {
        return $this->hasMany(SurveyRequest::class);
    }

    public function surveyRequestServices()
    {
        return $this->hasMany(SurveyRequestService::class);
    }

    // Tukang yang bisa mengerjakan service ini
    public function tukangs()
    {
        return $this->belongsToMany(User::class, 'tukang_services', 'service_id', 'tukang_id')
            ->withPivot('custom_price', 'notes')
            ->withTimestamps();
    }
}
