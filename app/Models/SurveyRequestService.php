<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyRequestService extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['estimated_price' => 'decimal:2'];

    public function surveyRequest()
    {
        return $this->belongsTo(SurveyRequest::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
