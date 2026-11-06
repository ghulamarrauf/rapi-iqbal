<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomNotification extends Model
{
    protected $fillable = ['user_id', 'event_id', 'message', 'is_read'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
