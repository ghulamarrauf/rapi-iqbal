@extends('layouts.app')

@section('title', 'Cari ruangan')

@section('content')
@php($me = auth()->user())
<div class="page-head">
    <div>
        <h1>Cari ruangan</h1>
        <p>Cari berdasarkan nama, lokasi, atau PIC, lalu lihat ketersediaannya pada tanggal tertentu.</p>
    </div>
</div>

<div class="panel mb-3">
    <form class="table-tools align-items-center" method="GET" action="{{ route('rooms.search') }}">
        <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" style="max-width: 260px"
               placeholder="Nama ruang, lokasi, atau PIC">
        <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="max-width: 170px">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="free" value="1" id="free" @checked($freeOnly)>
            <label class="form-check-label small" for="free">Hanya yang kosong</label>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Cari</button>
        @if($q !== '' || $freeOnly)
            <a href="{{ route('rooms.search', ['date' => $date]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </form>
</div>

<div class="row g-3">
    @forelse($rooms as $r)
        @php($slots = $booked->get($r->id, collect()))
        <div class="col-md-6 col-xl-4">
            <div class="panel h-100">
                <div class="panel-head">
                    <div>
                        <h2>{{ $r->name }}</h2>
                        <div class="text-secondary small">{{ $r->location ?: '-' }}</div>
                    </div>
                    <span class="tag {{ $slots->isEmpty() ? 'status-ok' : 'status-wait' }}">{{ $slots->isEmpty() ? 'Kosong' : 'Terpakai '.$slots->count().'x' }}</span>
                </div>
                <div class="panel-body">
                    <div class="small mb-2"><span class="text-secondary">PIC:</span> {{ $r->pic->name ?? 'Belum ditentukan' }}</div>
                    @forelse($slots as $s)
                        <div class="small">
                            <strong>{{ $s->time_range }}</strong> &middot; {{ $s->title }}
                            <span class="text-secondary">({{ $s->user->name }})</span>
                            @if($s->status === \App\Models\Event::MENUNGGU) <span class="tag status-wait">Menunggu</span> @endif
                        </div>
                    @empty
                        <div class="small text-secondary">Belum ada agenda pada tanggal ini.</div>
                    @endforelse
                    @if($me->isAdmin() || $me->isGuru())
                        <a href="{{ route('events.create', ['room_id' => $r->id, 'event_date' => $date]) }}" class="btn btn-outline-primary btn-sm mt-3">Ajukan peminjaman</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="panel"><div class="empty">Tidak ada ruangan yang cocok dengan pencarian.</div></div></div>
    @endforelse
</div>
@endsection
