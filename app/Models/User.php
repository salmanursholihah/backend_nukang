<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;



    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isTukang(): bool
    {
        return $this->role === 'tukang';
    }
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // ── Relasi sebagai Tukang ──────────────────────────────
    public function tukangProfile()
    {
        return $this->hasOne(TukangProfile::class);
    }

    public function tukangLocation()
    {
        return $this->hasOne(TukangLocation::class, 'tukang_id');
    }

    public function tukangServices()
    {
        return $this->belongsToMany(Service::class, 'tukang_services', 'tukang_id', 'service_id')
            ->withPivot('custom_price', 'notes')
            ->withTimestamps();
    }

    public function jobOrders()
    {
        return $this->hasMany(Order::class, 'tukang_id');
    }

    public function assignedSurveys()
    {
        return $this->hasMany(SurveyRequest::class, 'tukang_id');
    }

    public function earnings()
    {
        return $this->hasMany(PartnerEarning::class, 'tukang_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'tukang_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'tukang_id');
    }

    // ── Relasi sebagai Customer ────────────────────────────
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function surveyRequests()
    {
        return $this->hasMany(SurveyRequest::class, 'customer_id');
    }

    // ── Relasi Bersama ─────────────────────────────────────
    public function chatsAsCustomer()
    {
        return $this->hasMany(Chat::class, 'customer_id');
    }

    public function chatsAsTukang()
    {
        return $this->hasMany(Chat::class, 'tukang_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
