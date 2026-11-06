<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Laporan harian & bulanan: peminjaman ruang (berdasarkan tanggal agenda)
 * dan kerusakan fasilitas (berdasarkan tanggal laporan dibuat). Khusus Waka/Admin.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$mode, $start, $end, $label] = $this->period($request);

        $events     = $this->eventsQuery($start, $end)->get();
        $complaints = $this->complaintsQuery($start, $end)->get();

        return view('reports.index', [
            'mode'       => $mode,
            'date'       => $start->toDateString(),
            'month'      => $start->format('Y-m'),
            'label'      => $label,
            'events'     => $events,
            'complaints' => $complaints,
            'eventStats' => [
                'Total pengajuan' => $events->count(),
                'Disetujui'       => $events->where('status', Event::DISETUJUI)->count(),
                'Menunggu'        => $events->where('status', Event::MENUNGGU)->count(),
                'Ditolak'         => $events->where('status', Event::DITOLAK)->count(),
            ],
            'complaintStats' => [
                'Total laporan'       => $complaints->count(),
                'Belum diperbaiki'    => $complaints->where('status_repair', Complaint::BELUM)->count(),
                'Menunggu spare part' => $complaints->where('status_repair', Complaint::SPARE)->count(),
                'Sudah diperbaiki'    => $complaints->where('status_repair', Complaint::SELESAI)->count(),
            ],
            // Pemakaian ruang (hanya yang disetujui), diurutkan dari yang tersering
            'roomUsage' => $events->where('status', Event::DISETUJUI)
                ->groupBy(fn ($e) => $e->room->name)
                ->map->count()
                ->sortDesc(),
            'printedAt' => now()->locale('id')->translatedFormat('j F Y H:i'),
        ]);
    }

    /** Unduh CSV (bisa dibuka di Excel) untuk periode yang sama dengan halaman laporan. */
    public function download(Request $request, string $type)
    {
        [$mode, $start, $end] = $this->period($request);

        $name = 'laporan-'.$type.'-'.($mode === 'harian' ? $start->format('Y-m-d') : $start->format('Y-m')).'.csv';

        return response()->streamDownload(function () use ($type, $start, $end) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8

            if ($type === 'peminjaman') {
                fputcsv($out, ['No', 'Tanggal', 'Jam pelajaran', 'Ruang', 'Agenda', 'Pemohon', 'Status', 'Alasan penolakan', 'Ditinjau oleh']);
                foreach ($this->eventsQuery($start, $end)->get() as $i => $e) {
                    fputcsv($out, [
                        $i + 1, $e->event_date->format('d/m/Y'), $e->time_range, $e->room->name, $e->title,
                        $e->user->name, $e->status, $e->reject_reason, $e->reviewer->name ?? '',
                    ]);
                }
            } else {
                fputcsv($out, ['No', 'Tanggal lapor', 'Ruang', 'Pelapor', 'PIC', 'Agenda terkait', 'Kerusakan', 'Status']);
                foreach ($this->complaintsQuery($start, $end)->get() as $i => $c) {
                    fputcsv($out, [
                        $i + 1, $c->created_at->format('d/m/Y H:i'), $c->room->name, $c->reporter->name,
                        $c->picName(), $c->event->title ?? '', $c->description, $c->status_repair,
                    ]);
                }
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------

    /** @return array{0:string,1:Carbon,2:Carbon,3:string} mode, awal, akhir, label periode */
    private function period(Request $request): array
    {
        $mode = $request->query('mode') === 'bulanan' ? 'bulanan' : 'harian';

        if ($mode === 'bulanan') {
            try {
                $start = Carbon::createFromFormat('!Y-m', (string) $request->query('month', now()->format('Y-m')));
            } catch (\Throwable) {
                $start = now();
            }
            $start = $start->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $label = $start->copy()->locale('id')->translatedFormat('F Y');
        } else {
            try {
                $start = Carbon::createFromFormat('!Y-m-d', (string) $request->query('date', today()->toDateString()));
            } catch (\Throwable) {
                $start = today();
            }
            $start = $start->startOfDay();
            $end   = $start->copy()->endOfDay();
            $label = $start->copy()->locale('id')->translatedFormat('l, j F Y');
        }

        return [$mode, $start, $end, $label];
    }

    private function eventsQuery(Carbon $start, Carbon $end)
    {
        return Event::with(['room', 'user', 'reviewer'])
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('event_date')
            ->orderBy('start_time');
    }

    private function complaintsQuery(Carbon $start, Carbon $end)
    {
        return Complaint::with(['room.pic', 'pic', 'reporter', 'event'])
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('created_at');
    }
}
