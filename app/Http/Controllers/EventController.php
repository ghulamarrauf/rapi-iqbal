<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use App\Models\RoomNotification;
use App\Support\JamPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /** Admin melihat semua pengajuan, guru/organisasi hanya miliknya sendiri. */
    public function index(Request $request)
    {
        $user = $request->user();

        $events = Event::with(['room', 'user', 'previousRoom'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('event_date')
            ->orderBy('start_time')
            ->paginate(12);

        return view('events.index', [
            'events' => $events,
            'rooms'  => Room::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('events.form', [
            // room_id & event_date opsional, dikirim dari halaman Cari ruangan
            'event' => new Event($request->only('room_id', 'event_date')),
            'rooms' => Room::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $isAdmin = Auth::user()->isAdmin();

        $conflict = DB::transaction(function () use ($data, $isAdmin) {
            $conflict = $this->findConflict($data);
            if (! $conflict) {
                Event::create($data + [
                    'user_id'     => Auth::id(),
                    // Pengajuan Waka langsung disetujui; guru/organisasi menunggu persetujuan Waka
                    'status'      => $isAdmin ? Event::DISETUJUI : Event::MENUNGGU,
                    'reviewed_by' => $isAdmin ? Auth::id() : null,
                    'reviewed_at' => $isAdmin ? now() : null,
                ]);
            }

            return $conflict;
        });

        if ($conflict) {
            return $this->conflictResponse($conflict);
        }

        return redirect()->route('events.index')->with('success', $isAdmin
            ? 'Berhasil: agenda tersimpan dan langsung disetujui.'
            : 'Berhasil: pengajuan peminjaman terkirim dan menunggu persetujuan Waka. Anda akan mendapat notifikasi hasilnya.');
    }

    public function edit(Event $event)
    {
        $this->authorizeOwner($event);

        return view('events.form', [
            'event' => $event,
            'rooms' => Room::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeOwner($event);
        $data = $this->validated($request, $event);

        $conflict = DB::transaction(function () use ($data, $event) {
            $conflict = $this->findConflict($data, $event->id);
            if (! $conflict) {
                if (! Auth::user()->isAdmin()) {
                    // Perubahan oleh pemohon perlu ditinjau ulang
                    $data += [
                        'status'        => Event::MENUNGGU,
                        'reject_reason' => null,
                        'reviewed_by'   => null,
                        'reviewed_at'   => null,
                    ];
                }
                $event->update($data);
            }

            return $conflict;
        });

        if ($conflict) {
            return $this->conflictResponse($conflict);
        }

        return redirect()->route('events.index')->with('success', Auth::user()->isAdmin()
            ? 'Agenda berhasil diperbarui.'
            : 'Berhasil: perubahan tersimpan dan pengajuan kembali menunggu persetujuan Waka.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeOwner($event);
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Agenda dihapus.');
    }

    /**
     * Waka/Admin memindahkan agenda ke ruang lain (overriding).
     * Alasan wajib diisi, disimpan di kolom move_reason, dan sistem membuat
     * notifikasi otomatis untuk pemilik agenda berisi ruang baru + alasan.
     */
    public function move(Request $request, Event $event)
    {
        $data = $request->validate([
            'room_id'     => ['required', 'exists:rooms,id'],
            'move_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'move_reason.required' => 'Alasan pemindahan wajib diisi.',
            'move_reason.min'      => 'Alasan pemindahan minimal 5 karakter.',
        ]);

        // Ruang tujuan tidak boleh sama dengan ruang sekarang
        if ((int) $data['room_id'] === (int) $event->room_id) {
            return back()->withErrors(['room_id' => 'Ruang tujuan harus berbeda dengan ruang saat ini.']);
        }

        $conflict = DB::transaction(function () use ($data, $event) {
            $conflict = Event::overlapping(
                $data['room_id'], $event->event_date->toDateString(),
                $event->start_time, $event->end_time, $event->id
            )->lockForUpdate()->first();

            if ($conflict) {
                return $conflict;
            }

            $oldRoom = $event->room;
            $newRoom = Room::findOrFail($data['room_id']);

            $event->update([
                'moved_from_room_id' => $oldRoom->id,
                'room_id'            => $newRoom->id,
                'move_reason'        => $data['move_reason'],
                'moved_at'           => now(),
            ]);

            if ((int) $event->user_id !== (int) Auth::id()) {
                RoomNotification::create([
                    'user_id'  => $event->user_id,
                    'event_id' => $event->id,
                    'message'  => 'Agenda "'.$event->title.'" pada '
                        .$event->event_date->locale('id')->translatedFormat('j F Y').' pada '.$event->time_range
                        .' dipindahkan dari '.$oldRoom->name.' ke '.$newRoom->name
                        .'. Alasan: '.$data['move_reason'],
                ]);
            }

            return null;
        });

        if ($conflict) {
            return back()->with('error', 'Pemindahan dibatalkan: ruang tujuan sudah dipakai pada '
                .$conflict->time_range.' untuk agenda "'.$conflict->title.'".');
        }

        return redirect()->route('events.index')->with('success', 'Agenda dipindahkan dan pemilik agenda diberi notifikasi.');
    }

    /** Waka/Admin menyetujui pengajuan; pemohon mendapat notifikasi berhasil. */
    public function approve(Event $event)
    {
        if ($event->status === Event::DISETUJUI) {
            return back()->with('error', 'Pengajuan ini sudah disetujui.');
        }

        $conflict = DB::transaction(function () use ($event) {
            // Pastikan tidak ada agenda disetujui lain yang menempati ruang & jam yang sama
            $conflict = Event::overlapping(
                $event->room_id, $event->event_date->toDateString(),
                $event->start_time, $event->end_time, $event->id
            )->approved()->lockForUpdate()->first();

            if ($conflict) {
                return $conflict;
            }

            $event->update([
                'status'        => Event::DISETUJUI,
                'reject_reason' => null,
                'reviewed_by'   => Auth::id(),
                'reviewed_at'   => now(),
            ]);

            $this->notifyOwner($event, 'Pengajuan peminjaman BERHASIL disetujui: agenda "'.$event->title.'" di '
                .$event->room->name.', '.$this->dateText($event).' ('.$event->time_range.').');

            return null;
        });

        if ($conflict) {
            return back()->with('error', 'Gagal menyetujui: ruang sudah dipakai pada '.$conflict->time_range
                .' untuk agenda "'.$conflict->title.'".');
        }

        return back()->with('success', 'Pengajuan disetujui dan pemohon diberi notifikasi.');
    }

    /** Waka/Admin menolak pengajuan. Alasan wajib dan dikirim ke pemohon. */
    public function reject(Request $request, Event $event)
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi.',
            'reject_reason.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($event, $data) {
            $event->update([
                'status'        => Event::DITOLAK,
                'reject_reason' => $data['reject_reason'],
                'reviewed_by'   => Auth::id(),
                'reviewed_at'   => now(),
            ]);

            $this->notifyOwner($event, 'Pengajuan peminjaman TIDAK disetujui: agenda "'.$event->title.'" di '
                .$event->room->name.', '.$this->dateText($event).' ('.$event->time_range.'). Alasan: '.$data['reject_reason']);
        });

        return back()->with('success', 'Pengajuan ditolak dan alasan dikirim ke pemohon.');
    }

    /** Jam yang sudah terpakai pada ruang & tanggal tertentu (untuk form pengajuan). */
    public function availability(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'integer'],
            'date'    => ['required', 'date'],
            'except'  => ['nullable', 'integer'],
        ]);

        $slots = Event::with('user')
            ->where('room_id', $request->room_id)
            ->where('status', '!=', Event::DITOLAK)
            ->whereDate('event_date', $request->date)
            ->when($request->filled('except'), fn ($q) => $q->where('id', '!=', $request->except))
            ->orderBy('start_time')
            ->get()
            ->map(fn ($e) => [
                'start' => substr($e->start_time, 0, 5),
                'end'   => substr($e->end_time, 0, 5),
                'label' => $e->time_range,
                'title' => $e->title,
                'by'    => $e->user->name,
            ]);

        return response()->json($slots);
    }

    // ------------------------------------------------------------------

    private function validated(Request $request, ?Event $original = null): array
    {
        $max = max(JamPelajaran::numbers());

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            // "Event sekolah" hanya untuk Waka/Admin
            'category'     => ['required', 'in:'.implode(',', array_keys(Auth::user()->isAdmin()
                ? Event::KATEGORI
                : array_diff_key(Event::KATEGORI, array_flip(Event::KATEGORI_ADMIN))))],
            'description'  => ['nullable', 'string', 'max:1000'],
            'room_id'      => ['required', 'exists:rooms,id'],
            'event_date'   => ['required', 'date', 'after_or_equal:today'],
            'start_period' => ['required', 'integer', 'between:1,'.$max],
            'end_period'   => ['required', 'integer', 'between:1,'.$max, 'gte:start_period'],
        ], [
            'event_date.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'start_period.required'     => 'Jam pelajaran mulai wajib dipilih.',
            'end_period.required'       => 'Jam pelajaran selesai wajib dipilih.',
            'end_period.gte'            => 'Jam ke- selesai tidak boleh lebih kecil dari jam ke- mulai.',
        ]);

        // Jam pelajaran yang sudah lewat tidak boleh dipilih. Saat mengubah agenda, pengecekan
        // dilewati bila tanggal & jam mulai tidak diubah (mis. hanya memperbaiki judul).
        $startNo = (int) $data['start_period'];
        $date    = Carbon::parse($data['event_date'])->toDateString();
        $unchanged = $original
            && $original->event_date->toDateString() === $date
            && substr((string) $original->start_time, 0, 5) === JamPelajaran::JAM[$startNo][0];

        if (! $unchanged && JamPelajaran::hasEnded($startNo, $date)) {
            throw ValidationException::withMessages([
                'start_period' => 'Jam ke-'.$startNo.' pada tanggal tersebut sudah lewat. Pilih jam yang belum berakhir.',
            ]);
        }

        // Jam pelajaran diterjemahkan ke kolom TIME (HH:MM:SS) di database
        $data['start_time'] = JamPelajaran::startTime((int) $data['start_period']);
        $data['end_time']   = JamPelajaran::endTime((int) $data['end_period']);
        unset($data['start_period'], $data['end_period']);

        return $data;
    }

    private function findConflict(array $data, ?int $ignoreId = null): ?Event
    {
        return Event::overlapping(
            $data['room_id'], $data['event_date'], $data['start_time'], $data['end_time'], $ignoreId
        )->lockForUpdate()->first();
    }

    private function conflictResponse(Event $conflict)
    {
        $conflict->loadMissing('room');

        return back()->withInput()->withErrors([
            'conflict' => $conflict->room->name.' sudah dipakai pada '.$conflict->time_range
                .' untuk agenda "'.$conflict->title.'". Pilih jam atau ruang lain.',
        ]);
    }

    /** Notifikasi untuk pemilik agenda (tidak dikirim jika pemiliknya yang meninjau sendiri). */
    private function notifyOwner(Event $event, string $message): void
    {
        if ((int) $event->user_id === (int) Auth::id()) {
            return;
        }

        RoomNotification::create([
            'user_id'  => $event->user_id,
            'event_id' => $event->id,
            'message'  => $message,
        ]);
    }

    private function dateText(Event $event): string
    {
        return $event->event_date->locale('id')->translatedFormat('j F Y');
    }

    private function authorizeOwner(Event $event): void
    {
        abort_unless(Auth::user()->isAdmin() || (int) $event->user_id === (int) Auth::id(), 403);
    }
}
