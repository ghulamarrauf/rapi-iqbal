@extends('layouts.app')

@section('title', 'Kalender agenda')

@section('content')
<div class="page-head">
    <div>
        <h1>Kalender agenda sekolah</h1>
        <p>Klik tanggal untuk melihat daftar agenda hari itu.</p>
    </div>
    <div class="cal-nav">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('calendar', ['month' => $prevMonth]) }}" aria-label="Bulan sebelumnya"><i class="bi bi-chevron-left"></i></a>
        <div class="cal-title">{{ $month->translatedFormat('F Y') }}</div>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('calendar', ['month' => $nextMonth]) }}" aria-label="Bulan berikutnya"><i class="bi bi-chevron-right"></i></a>
        <a class="btn btn-outline-secondary btn-sm ms-1" href="{{ route('calendar', ['month' => $currentMonth]) }}">Hari ini</a>
    </div>
</div>

<div class="cal-legend">
    <span><i class="sw cat-siswa"></i> Event siswa</span>
    <span><i class="sw cat-guru"></i> Event guru</span>
    <span><i class="sw cat-pinjam"></i> Peminjaman ruang</span>
    <span><i class="sw cat-sekolah"></i> Event sekolah</span>
    <span><i class="sw cal-nat"></i> Libur nasional / hari besar</span>
    <span><i class="sw sw-off">Libur</i> Sabtu &amp; Minggu</span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel">
            <div class="cal-grid">
                @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dow)
                    <div class="cal-dow">{{ $dow }}</div>
                @endforeach

                @foreach($weeks as $week)
                    @foreach($week as $day)
                        @php
                            $key = $day->toDateString();
                            $list = $events->get($key, collect());
                            $nat = $national->get($key, []);
                            $isWeekend = $day->isWeekend();
                            $isOff = $isWeekend || collect($nat)->contains('red', true);
                        @endphp
                        <a href="{{ route('calendar', ['month' => $month->format('Y-m'), 'date' => $key]) }}"
                           class="cal-day {{ $day->month !== $month->month ? 'other' : '' }} {{ $day->isToday() ? 'today' : '' }} {{ $key === $selected->toDateString() ? 'selected' : '' }} {{ $isOff ? 'off' : '' }}">
                            <span class="d">{{ $day->day }}</span>
                            @if($isWeekend)<span class="cal-lbl">Libur</span>@endif
                            @if($nat)
                                <span class="cal-nat" title="{{ $nat[0]['type'] }}: {{ $nat[0]['name'] }}">{{ $nat[0]['name'] }}</span>
                            @endif
                            @foreach($list->take(2) as $e)
                                <span class="cal-ev {{ $e->categoryClass() }}" title="{{ $e->categoryLabel() }}">JP{{ $e->start_period }} {{ $e->title }}</span>
                            @endforeach
                            @if($list->count() > 2)
                                <span class="cal-more">+{{ $list->count() - 2 }} lagi</span>
                            @endif
                            @if($list->isNotEmpty())
                                <span class="cal-count">{{ $list->count() }}</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-head">
                <h2>{{ $selected->translatedFormat('l, j F Y') }}</h2>
                <span class="text-secondary small">{{ $agenda->count() }} agenda</span>
            </div>
            @php($selNational = $national->get($selected->toDateString(), []))
            @foreach($selNational as $n)
                <div class="day-note nat"><strong>{{ $n['type'] }}:</strong> {{ $n['name'] }}</div>
            @endforeach
            @if($selected->isWeekend())
                <div class="day-note off"><strong>Hari libur:</strong> {{ $selected->translatedFormat('l') }} (akhir pekan)</div>
            @endif
            @forelse($agenda as $e)
                <div class="agenda-item">
                    <span class="cat-tag {{ $e->categoryClass() }}">{{ $e->categoryLabel() }}</span>
                    <div class="t">{{ $e->title }}</div>
                    <div class="m">{{ $e->time_range }} &middot; {{ $e->room->name }}</div>
                    <div class="m">Penyelenggara: {{ $e->user->name }}</div>
                    @if($e->description)
                        <div class="mt-1 small">{{ $e->description }}</div>
                    @endif
                </div>
            @empty
                <div class="empty">Tidak ada agenda pada tanggal ini.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
