@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="page-head no-print">
    <div>
        <h1>Laporan {{ $mode }}</h1>
        <p>Peminjaman ruang dan kerusakan fasilitas &middot; {{ $label }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
        <a href="{{ route('reports.download', ['type' => 'peminjaman'] + request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> CSV peminjaman</a>
        <a href="{{ route('reports.download', ['type' => 'kerusakan'] + request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> CSV kerusakan</a>
    </div>
</div>

<div class="panel mb-3 no-print">
    <form class="table-tools align-items-center" method="GET" action="{{ route('reports.index') }}">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('reports.index', ['mode' => 'harian', 'date' => $date]) }}"
               class="btn {{ $mode === 'harian' ? 'btn-primary' : 'btn-outline-secondary' }}">Harian</a>
            <a href="{{ route('reports.index', ['mode' => 'bulanan', 'month' => $month]) }}"
               class="btn {{ $mode === 'bulanan' ? 'btn-primary' : 'btn-outline-secondary' }}">Bulanan</a>
        </div>
        <input type="hidden" name="mode" value="{{ $mode }}">
        @if($mode === 'harian')
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="max-width: 180px" onchange="this.form.submit()">
        @else
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="max-width: 180px" onchange="this.form.submit()">
        @endif
    </form>
</div>

<div class="print-title">
    <h2>Laporan {{ $mode }} RAPI-Sekolah</h2>
    <div>Periode: {{ $label }} &middot; dicetak {{ $printedAt }}</div>
</div>

<h2 class="h6 fw-semibold mb-2">Peminjaman ruang</h2>
<div class="row g-3 mb-3">
    @foreach($eventStats as $l => $v)
        <div class="col-6 col-lg-3"><div class="stat"><div class="n">{{ $v }}</div><div class="l">{{ $l }}</div></div></div>
    @endforeach
</div>

<div class="panel mb-4">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Tanggal</th><th>Jam</th><th>Ruang</th><th>Agenda</th><th>Pemohon</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($events as $e)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td class="text-nowrap">{{ $e->event_date->format('d/m/Y') }}</td>
                    <td class="text-nowrap">{{ $e->time_range }}</td>
                    <td>{{ $e->room->name }}</td>
                    <td>{{ $e->title }}</td>
                    <td>{{ $e->user->name }}</td>
                    <td>
                        <span class="tag {{ $e->badgeClass() }}">{{ $e->status }}</span>
                        @if($e->status === \App\Models\Event::DITOLAK && $e->reject_reason)
                            <div class="small text-secondary mt-1">{{ $e->reject_reason }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada peminjaman pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($roomUsage->isNotEmpty())
        <div class="panel-body border-top small">
            <strong>Pemakaian ruang (disetujui):</strong>
            @foreach($roomUsage as $name => $count)
                {{ $name }} ({{ $count }}x){{ $loop->last ? '' : ',' }}
            @endforeach
        </div>
    @endif
</div>

<h2 class="h6 fw-semibold mb-2">Kerusakan fasilitas</h2>
<div class="row g-3 mb-3">
    @foreach($complaintStats as $l => $v)
        <div class="col-6 col-lg-3"><div class="stat"><div class="n">{{ $v }}</div><div class="l">{{ $l }}</div></div></div>
    @endforeach
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Tanggal lapor</th><th>Ruang</th><th>Pelapor</th><th>PIC</th><th>Kerusakan</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($complaints as $c)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td class="text-nowrap">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $c->room->name }}</td>
                    <td>{{ $c->reporter->name }}</td>
                    <td>{{ $c->picName() }}</td>
                    <td>
                        {{ $c->description }}
                        @if($c->event)
                            <div class="small text-secondary">Saat peminjaman: {{ $c->event->title }}</div>
                        @endif
                    </td>
                    <td><span class="tag {{ $c->badgeClass() }}">{{ $c->status_repair }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada laporan kerusakan pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
