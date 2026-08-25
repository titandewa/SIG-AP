<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'signature_path',
        'force_password_reset',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi (JSON/Array).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Type casting atribut.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function notificationHistories()
    {
        return $this->hasMany(NotificationHistory::class)->latest();
    }
    
}