<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $today = today()->toDateString();

        $upcoming = fn () => Event::with(['room', 'user'])
            ->approved()
            ->whereDate('event_date', '>=', $today)
            ->orderBy('event_date')
            ->orderBy('start_time');

        $data = [
            'user'        => $user,
            'todayEvents' => Event::with(['room', 'user'])
                ->approved()
                ->whereDate('event_date', $today)
                ->orderBy('start_time')
                ->get(),
        ];

        if ($user->isAdmin()) {
            $data['stats'] = [
                ['label' => 'Pengguna',          'value' => User::count()],
                ['label' => 'Ruang',             'value' => Room::count()],
                ['label' => 'Agenda mendatang',  'value' => $upcoming()->count()],
                ['label' => 'Belum diperbaiki',  'value' => Complaint::where('status_repair', Complaint::BELUM)->count()],
            ];
            $data['upcoming'] = $upcoming()->limit(6)->get();
            $data['overdue']  = Complaint::with(['room.pic'])->overdue()->oldest()->limit(5)->get();
        } elseif ($user->isPic()) {
            $roomIds   = $user->rooms()->pluck('id');
            $complaints = Complaint::whereIn('room_id', $roomIds);

            $data['stats'] = [
                ['label' => 'Ruang dipegang',     'value' => $roomIds->count()],
                ['label' => 'Belum diperbaiki',   'value' => (clone $complaints)->where('status_repair', Complaint::BELUM)->count()],
                ['label' => 'Menunggu spare part', 'value' => (clone $complaints)->where('status_repair', Complaint::SPARE)->count()],
                ['label' => 'Sudah diperbaiki',   'value' => (clone $complaints)->where('status_repair', Complaint::SELESAI)->count()],
            ];
            $data['myRooms']    = $user->rooms()->orderBy('name')->get();
            $data['picQueue']   = Complaint::with(['room', 'reporter'])
                ->whereIn('room_id', $roomIds)
                ->where('status_repair', '!=', Complaint::SELESAI)
                ->oldest()->limit(6)->get();
        } else {
            $mine = Event::where('user_id', $user->id);
            $data['stats'] = [
                ['label' => 'Agenda saya',       'value' => (clone $mine)->count()],
                ['label' => 'Agenda mendatang',  'value' => (clone $mine)->whereDate('event_date', '>=', $today)->count()],
                ['label' => 'Pengaduan saya',    'value' => Complaint::where('reporter_id', $user->id)->count()],
                ['label' => 'Belum selesai',     'value' => Complaint::where('reporter_id', $user->id)->where('status_repair', '!=', Complaint::SELESAI)->count()],
            ];
            $data['myUpcoming'] = (clone $mine)->with('room')
                ->whereDate('event_date', '>=', $today)
                ->orderBy('event_date')->orderBy('start_time')
                ->limit(6)->get();
        }

        return view('dashboard', $data);
    }
}
