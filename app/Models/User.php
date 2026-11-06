<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 1; // Waka / Admin
    public const ROLE_GURU  = 2; // Guru / Organisasi
    public const ROLE_PIC   = 3; // PIC Ruang

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return (int) $this->role_id === self::ROLE_GURU;
    }

    public function isPic(): bool
    {
        return (int) $this->role_id === self::ROLE_PIC;
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /** Ruang yang didelegasikan ke user ini (khusus PIC Ruang). */
    public function rooms()
    {
        return $this->hasMany(Room::class, 'pic_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }

    public function roomNotifications()
    {
        return $this->hasMany(RoomNotification::class, 'user_id');
    }
}
