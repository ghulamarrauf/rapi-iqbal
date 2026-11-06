<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\KalenderNasional;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Kalender agenda sekolah. Publik dan hanya-lihat: tidak ada aksi tulis di sini.
 */
class CalendarController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = Carbon::createFromFormat('!Y-m', (string) $request->query('month', now()->format('Y-m')));
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }
        $month = $month->startOfMonth()->locale('id');

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $events = Event::with(['room', 'user'])
            ->approved()
            ->whereBetween('event_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($e) => $e->event_date->toDateString());

        $days = collect();
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $days->push($d->copy());
        }

        // Tanggal terpilih: dari query, jika valid dan masih di dalam grid; kalau tidak, hari ini / tanggal 1
        $selected = $request->query('date');
        if (! is_string($selected) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected)
            || $selected < $gridStart->toDateString() || $selected > $gridEnd->toDateString()) {
            $selected = today()->between($month, $month->copy()->endOfMonth())
                ? today()->toDateString()
                : $month->toDateString();
        }

        // Hari libur nasional / cuti bersama / hari besar nasional per tanggal pada grid
        $national = $days->mapWithKeys(fn ($d) => [$d->toDateString() => KalenderNasional::on($d->toDateString())])
            ->filter();

        return view('calendar.index', [
            'national'     => $national,
            'month'        => $month,
            'weeks'        => $days->chunk(7),
            'events'       => $events,
            'selected'     => Carbon::parse($selected)->locale('id'),
            'agenda'       => $events->get($selected, collect()),
            'prevMonth'    => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth'    => $month->copy()->addMonth()->format('Y-m'),
            'currentMonth' => now()->format('Y-m'),
        ]);
    }
}
