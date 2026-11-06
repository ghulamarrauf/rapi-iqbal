<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model
{
    use HasFactory;

    public const BELUM   = 'Belum Diperbaiki';
    public const SPARE   = 'Menunggu Spare Part';
    public const SELESAI = 'Sudah Diperbaiki';

    /** Batas hari sebelum laporan yang belum ditangani boleh dieskalasi Waka. */
    public const ESCALATION_DAYS = 3;

    public const STATUSES = [self::BELUM, self::SPARE, self::SELESAI];

    protected $fillable = [
        'room_id',
        'reporter_id',
        'event_id',
        'pic_id',
        'description',
        'photo_path',
        'repair_photo_path',
        'status_repair',
        'repaired_at',
    ];

    protected function casts(): array
    {
        return ['repaired_at' => 'datetime'];
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /** PIC yang tercatat saat laporan dibuat (cadangan: PIC ruang saat ini). */
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function picName(): string
    {
        return $this->pic->name ?? $this->room->pic->name ?? 'Belum ditentukan';
    }

    public function scopeOverdue($query)
    {
        return $query->where('status_repair', self::BELUM)
            ->where('created_at', '<=', now()->subDays(self::ESCALATION_DAYS));
    }

    public function isOverdue(): bool
    {
        return $this->status_repair === self::BELUM
            && $this->created_at->lte(now()->subDays(self::ESCALATION_DAYS));
    }

    public function badgeClass(): string
    {
        return match ($this->status_repair) {
            self::SELESAI => 'status-ok',
            self::SPARE   => 'status-wait',
            default       => 'status-bad',
        };
    }

    /** Tautan WhatsApp ke nomor PIC ruang (null jika PIC/nomor belum diisi). */
    public function whatsappUrl(): ?string
    {
        $pic = $this->room?->pic;

        if (! $pic || ! $pic->phone) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $pic->phone);
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        $days = (int) floor($this->created_at->diffInDays(now()));
        $text = "Yth. {$pic->name}, laporan kerusakan di {$this->room->name} "
            .'("'.Str::limit($this->description, 60).'") sudah '.$days
            .' hari berstatus Belum Diperbaiki. Mohon segera ditindaklanjuti dan diperbarui statusnya di RAPI-Sekolah.';

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }
}
