<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        return view('rooms.index', [
            'q'     => $q,
            'rooms' => Room::with('pic')->withCount('complaints')
                ->when($q !== '', function ($query) use ($q) {
                    $like = '%'.addcslashes($q, '%_\\').'%';
                    $query->where(function ($w) use ($like) {
                        $w->where('name', 'like', $like)
                            ->orWhere('location', 'like', $like)
                            ->orWhereHas('pic', fn ($p) => $p->where('name', 'like', $like));
                    });
                })
                ->orderBy('name')->paginate(12)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('rooms.form', ['room' => new Room(), 'pics' => $this->pics()]);
    }

    public function store(Request $request)
    {
        Room::create($this->validated($request));

        return redirect()->route('rooms.index')->with('success', 'Ruang ditambahkan.');
    }

    public function edit(Room $room)
    {
        return view('rooms.form', ['room' => $room, 'pics' => $this->pics()]);
    }

    public function update(Request $request, Room $room)
    {
        $room->update($this->validated($request, $room));

        return redirect()->route('rooms.index')->with('success', 'Data ruang diperbarui.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruang dihapus beserta agenda dan pengaduan di dalamnya.');
    }

    private function pics()
    {
        return User::where('role_id', User::ROLE_PIC)->withCount('rooms')->orderBy('name')->get();
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room?->id)],
            'location' => ['nullable', 'string', 'max:255'],
            // PIC harus user ber-role PIC Ruang
            'pic_id'   => ['nullable', Rule::exists('users', 'id')->where('role_id', User::ROLE_PIC)],
        ], [
            'name.unique'  => 'Nama ruang sudah ada.',
            'pic_id.exists' => 'PIC yang dipilih tidak valid.',
        ]);
    }
}
