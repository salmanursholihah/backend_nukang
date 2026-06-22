<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property int                             $order_progress_id
 * @property string                          $photo_path
 * @property string                          $photo_url
 * @property \Carbon\Carbon                  $created_at
 * @property \Carbon\Carbon                  $updated_at
 *
 * @property-read \App\Models\OrderProgress                      $progress
 */
class OrderProgressPhoto extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function progress()
    {
        return $this->belongsTo(OrderProgress::class, 'order_progress_id');
    }
}

