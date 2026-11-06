<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use App\Support\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    /**
     * Admin: semua laporan. PIC: hanya laporan pada ruang yang didelegasikan padanya.
     * Guru/Organisasi: laporan yang ia kirim sendiri.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Complaint::with(['room.pic', 'pic', 'reporter']);

        if ($user->isPic()) {
            $query->whereIn('room_id', $user->rooms()->pluck('id'));
        } elseif ($user->isGuru()) {
            $query->where('reporter_id', $user->id);
        }

        // Bawaan: hanya laporan yang belum selesai (Belum Diperbaiki + Menunggu Spare Part).
        // ?status=semua menampilkan semuanya; atau pilih satu status tertentu.
        $filter = $request->query('status');
        if (in_array($filter, Complaint::STATUSES, true)) {
            $query->where('status_repair', $filter);
        } elseif ($filter !== 'semua') {
            $filter = 'belum';
            $query->where('status_repair', '!=', Complaint::SELESAI);
        }

        return view('complaints.index', [
            'complaints' => $query->latest()->paginate(12)->withQueryString(),
            'filter'     => $filter,
        ]);
    }

    /**
     * Form pengaduan. Bila dibuka dari tombol "Lapor kerusakan" pada peminjaman (?event_id=),
     * ruang, pelapor, dan PIC terisi otomatis dari data peminjaman tersebut.
     */
    public function create(Request $request)
    {
        $event = null;
        if ($request->filled('event_id')) {
            $event = Event::with('room.pic')
                ->where('user_id', Auth::id())
                ->findOrFail($request->event_id);
        }

        return view('complaints.create', [
            'rooms' => Room::with('pic')->orderBy('name')->get(),
            'event' => $event,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id'     => ['required', 'exists:rooms,id'],
            'event_id'    => ['nullable', 'integer'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'photo'       => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
        ], [
            'photo.required'      => 'Foto bukti kerusakan wajib diunggah.',
            'photo.image'         => 'File harus berupa gambar.',
            'photo.max'           => 'Ukuran foto maksimal 8 MB.',
            'description.min'     => 'Jelaskan kerusakannya minimal 10 karakter.',
        ]);

        // Peminjaman yang dikaitkan harus milik pelapor dan ruangnya harus sama
        $event = null;
        if (! empty($data['event_id'])) {
            $event = Event::where('user_id', Auth::id())->findOrFail($data['event_id']);
            abort_unless((int) $event->room_id === (int) $data['room_id'], 422, 'Ruang tidak sesuai dengan peminjaman.');
        }

        // PIC diisi otomatis dari PIC ruang yang dipilih
        $room = Room::findOrFail($data['room_id']);

        Complaint::create([
            'room_id'       => $data['room_id'],
            'reporter_id'   => Auth::id(),
            'event_id'      => $event?->id,
            'pic_id'        => $room->pic_id,
            'description'   => $data['description'],
            'photo_path'    => ImageCompressor::store($request->file('photo')),
            'status_repair' => Complaint::BELUM,
        ]);

        return redirect()->route('complaints.index')->with('success', 'Berhasil: laporan kerusakan terkirim ke PIC ruangan'.($room->pic ? ' ('.$room->pic->name.').' : '.'));
    }

    public function show(Complaint $complaint)
    {
        $this->authorizeView($complaint);

        return view('complaints.show', [
            'complaint' => $complaint->load(['room.pic', 'pic', 'reporter', 'event']),
            'canUpdate' => $this->isPicOfRoom($complaint),
        ]);
    }

    /** Hanya PIC yang memegang ruang tersebut yang boleh memperbarui status. */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        abort_unless($this->isPicOfRoom($complaint), 403, 'Ruang ini bukan tanggung jawab Anda.');

        // Laporan yang sudah diperbaiki bersifat final dan tidak bisa diubah lagi
        if ($complaint->status_repair === Complaint::SELESAI) {
            return redirect()->route('complaints.show', $complaint)
                ->with('error', 'Laporan ini sudah berstatus Sudah Diperbaiki dan tidak dapat diubah lagi.');
        }

        $done = $request->input('status_repair') === Complaint::SELESAI;

        $rules = ['status_repair' => ['required', 'in:'.implode(',', Complaint::STATUSES)]];
        if ($done) {
            // Foto bukti wajib saat menandai selesai (kecuali foto sudah pernah diunggah)
            $rules['repair_photo'] = [
                $complaint->repair_photo_path ? 'nullable' : 'required',
                'image', 'mimes:jpeg,png,jpg,webp', 'max:8192',
            ];
        }

        $data = $request->validate($rules, [
            'repair_photo.required' => 'Foto hasil perbaikan wajib diunggah sebelum menandai Sudah Diperbaiki.',
            'repair_photo.image'    => 'File harus berupa gambar.',
            'repair_photo.max'      => 'Ukuran foto maksimal 8 MB.',
        ]);

        $update = [
            'status_repair' => $data['status_repair'],
            'repaired_at'   => $done ? ($complaint->repaired_at ?? now()) : null,
        ];

        if ($done && $request->hasFile('repair_photo')) {
            if ($complaint->repair_photo_path) {
                Storage::disk('public')->delete($complaint->repair_photo_path);
            }
            $update['repair_photo_path'] = ImageCompressor::store($request->file('repair_photo'), 'repairs');
        }

        $complaint->update($update);

        return redirect()->route('complaints.show', $complaint)->with('success', 'Status perbaikan diperbarui.');
    }

    // ------------------------------------------------------------------

    private function isPicOfRoom(Complaint $complaint): bool
    {
        $user = Auth::user();

        return $user->isPic()
            && $complaint->room
            && (int) $complaint->room->pic_id === (int) $user->id;
    }

    private function authorizeView(Complaint $complaint): void
    {
        $user = Auth::user();

        $allowed = $user->isAdmin()
            || $this->isPicOfRoom($complaint)
            || ($user->isGuru() && (int) $complaint->reporter_id === (int) $user->id);

        abort_unless($allowed, 403);
    }
}
