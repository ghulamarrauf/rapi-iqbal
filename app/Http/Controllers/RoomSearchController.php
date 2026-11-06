<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Pencarian ruangan untuk semua pengguna yang login: cari berdasarkan nama/lokasi/PIC
 * dan lihat ketersediaannya pada tanggal tertentu.
 */
class RoomSearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        try {
            $date = Carbon::createFromFormat('!Y-m-d', (string) $request->query('date', today()->toDateString()));
        } catch (\Throwable) {
            $date = today();
        }
        $freeOnly = $request->boolean('free');

        $rooms = Room::with('pic')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $query->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhereHas('pic', fn ($p) => $p->where('name', 'like', $like));
                });
            })
            ->orderBy('name')
            ->get();

        // Agenda (disetujui & menunggu) pada tanggal terpilih, dikelompokkan per ruang
        $booked = Event::with('user')
            ->where('status', '!=', Event::DITOLAK)
            ->whereDate('event_date', $date->toDateString())
            ->whereIn('room_id', $rooms->pluck('id'))
            ->orderBy('start_time')
            ->get()
            ->groupBy('room_id');

        if ($freeOnly) {
            $rooms = $rooms->filter(fn ($r) => ! $booked->has($r->id))->values();
        }

        return view('rooms.search', [
            'rooms'    => $rooms,
            'booked'   => $booked,
            'q'        => $q,
            'date'     => $date->toDateString(),
            'freeOnly' => $freeOnly,
        ]);
    }
}
