<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    /** Masa berlaku OTP (menit) dan batas salah input. */
    public const TTL_MINUTES  = 5;
    public const MAX_ATTEMPTS = 5;

    protected $fillable = ['user_id', 'code', 'attempts', 'expires_at'];

    protected $hidden = ['code'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function isUsable(): bool
    {
        return $this->expires_at->isFuture() && $this->attempts < self::MAX_ATTEMPTS;
    }
}
