<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\JamPelajaran;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public const MENUNGGU  = 'Menunggu';
    public const DISETUJUI = 'Disetujui';
    public const DITOLAK   = 'Ditolak';

    /** Jenis agenda di kalender: kunci => label. "Sekolah" hanya boleh dipilih Waka/Admin. */
    public const KATEGORI = [
        'Siswa'      => 'Event siswa',
        'Guru'       => 'Event guru',
        'Peminjaman' => 'Peminjaman ruang',
        'Sekolah'    => 'Event sekolah',
    ];
    public const KATEGORI_ADMIN = ['Sekolah'];

    protected $fillable = [
        'category',
        'status',
        'reject_reason',
        'reviewed_by',
        'reviewed_at',
        'user_id',
        'room_id',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'move_reason',
        'moved_from_room_id',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'moved_at'   => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'event_id');
    }

    /** Kelas CSS penanda warna kalender per jenis agenda. */
    public function categoryClass(): string
    {
        return match ($this->category) {
            'Siswa'      => 'cat-siswa',
            'Peminjaman' => 'cat-pinjam',
            'Sekolah'    => 'cat-sekolah',
            default      => 'cat-guru',
        };
    }

    public function categoryLabel(): string
    {
        return self::KATEGORI[$this->category] ?? self::KATEGORI['Guru'];
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            self::DISETUJUI => 'status-ok',
            self::DITOLAK   => 'status-bad',
            default         => 'status-wait',
        };
    }

    public function isApproved(): bool
    {
        return $this->status === self::DISETUJUI;
    }

    /** Hanya agenda yang sudah disetujui yang tampil di kalender publik & dashboard. */
    public function scopeApproved($query)
    {
        return $query->where('status', self::DISETUJUI);
    }

    public function previousRoom()
    {
        return $this->belongsTo(Room::class, 'moved_from_room_id');
    }

    /** Jam pelajaran awal (1-10), diturunkan dari kolom start_time. */
    public function getStartPeriodAttribute(): ?int
    {
        return JamPelajaran::fromStart($this->start_time);
    }

    /** Jam pelajaran akhir (1-10), diturunkan dari kolom end_time. */
    public function getEndPeriodAttribute(): ?int
    {
        return JamPelajaran::fromEnd($this->end_time);
    }

    /** Rentang untuk ditampilkan, mis. "Jam ke-2 s/d ke-4". */
    public function getTimeRangeAttribute(): string
    {
        $from = $this->start_period;
        $to   = max($from, (int) $this->end_period);

        return JamPelajaran::range($from, $to);
    }

    /**
     * Agenda lain di ruang & tanggal yang sama dengan rentang waktu beririsan.
     * Dua rentang beririsan jika: mulai_lama < selesai_baru DAN selesai_lama > mulai_baru.
     * (Agenda yang selesai tepat saat agenda lain mulai tidak dianggap bentrok.)
     */
    public function scopeOverlapping($query, $roomId, $date, $start, $end, $ignoreId = null)
    {
        // Pengajuan yang ditolak tidak memblokir ruang
        return $query->where('room_id', $roomId)
            ->where('status', '!=', self::DITOLAK)
            ->whereDate('event_date', $date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));
    }
}
