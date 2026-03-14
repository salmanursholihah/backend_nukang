<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyRequest extends Model
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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
