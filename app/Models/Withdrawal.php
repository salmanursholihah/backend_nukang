<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
        'iris_response' => 'array',
    ];

    public function tukang()
    {
        return $this->belongsTo(User::class, 'tukang_id');
    }
//  =========================================================================
    // STATUS HELPERS
    // Status: pending | processing | success | failed
    // =========================================================================
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isSuccess(): bool    { return $this->status === 'success'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }

    // Label untuk tampilan
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Menunggu',
            'processing' => 'Diproses Iris',
            'success'    => 'Berhasil',
            'failed'     => 'Gagal',
            default      => $this->status,
        };
    }

    // Label status Iris
    public function getIrisStatusLabelAttribute(): string
    {
        return match ($this->iris_status) {
            'queued'    => 'Antri',
            'processed' => 'Selesai',
            'failed'    => 'Gagal',
            default     => $this->iris_status ?? '-',
        };
    }
}
