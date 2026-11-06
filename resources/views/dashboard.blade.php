@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>{{ $user->name }} &middot; {{ $user->role->name }}</p>
    </div>
    @if($user->isAdmin() || $user->isGuru())
        <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Ajukan peminjaman</a>
    @endif
</div>

<div class="row g-3 mb-3">
    @foreach($stats as $s)
        <div class="col-6 col-lg-3">
            <div class="stat">
                <div class="n">{{ $s['value'] }}</div>
                <div class="l">{{ $s['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="panel h-100">
            <div class="panel-head"><h2>Agenda hari ini</h2><a href="{{ route('calendar') }}" class="small">Buka kalender</a></div>
            @forelse($todayEvents as $e)
                <div class="agenda-item">
                    <div class="t">{{ $e->title }}</div>
                    <div class="m">{{ $e->time_range }} &middot; {{ $e->room->name }} &middot; {{ $e->user->name }}</div>
                </div>
            @empty
                <div class="empty">Tidak ada agenda hari ini.</div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-6">
        @if($user->isAdmin())
            <div class="panel mb-3">
                <div class="panel-head"><h2>Laporan belum ditangani lewat batas {{ \App\Models\Complaint::ESCALATION_DAYS }} hari</h2></div>
                @forelse($overdue as $c)
                    <div class="agenda-item d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="t">{{ $c->room->name }}</div>
                            <div class="m">{{ \Illuminate\Support\Str::limit($c->description, 60) }}</div>
                            <div class="m">PIC: {{ $c->room->pic->name ?? 'belum ditentukan' }} &middot; {{ $c->created_at->diffForHumans() }}</div>
                        </div>
                        @if($url = $c->whatsappUrl())
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm"><i class="bi bi-whatsapp"></i> Ingatkan</a>
                        @endif
                    </div>
                @empty
                    <div class="empty">Tidak ada laporan yang melewati batas.</div>
                @endforelse
            </div>
            <div class="panel">
                <div class="panel-head"><h2>Agenda mendatang</h2></div>
                @forelse($upcoming as $e)
                    <div class="agenda-item">
                        <div class="t">{{ $e->title }}</div>
                        <div class="m">{{ $e->event_date->format('d/m/Y') }}, {{ $e->time_range }} &middot; {{ $e->room->name }}</div>
                    </div>
                @empty
                    <div class="empty">Belum ada agenda.</div>
                @endforelse
            </div>

        @elseif($user->isPic())
            <div class="panel mb-3">
                <div class="panel-head"><h2>Perlu ditindaklanjuti</h2><a href="{{ route('complaints.index', ['status' => 'semua']) }}" class="small">Semua laporan</a></div>
                @forelse($picQueue as $c)
                    <a href="{{ route('complaints.show', $c) }}" class="agenda-item d-block text-decoration-none text-reset">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="t">{{ $c->room->name }}</span>
                            <span class="tag {{ $c->badgeClass() }}">{{ $c->status_repair }}</span>
                        </div>
                        <div class="m">{{ \Illuminate\Support\Str::limit($c->description, 70) }}</div>
                    </a>
                @empty
                    <div class="empty">Semua laporan pada ruangan Anda sudah diperbaiki.</div>
                @endforelse
            </div>
            <div class="panel">
                <div class="panel-head"><h2>Ruangan yang Anda pegang</h2></div>
                @forelse($myRooms as $r)
                    <div class="agenda-item"><span class="t">{{ $r->name }}</span> <span class="m">{{ $r->location }}</span></div>
                @empty
                    <div class="empty">Belum ada ruang yang didelegasikan kepada Anda.</div>
                @endforelse
            </div>

        @else
            <div class="panel">
                <div class="panel-head"><h2>Agenda saya mendatang</h2><a href="{{ route('events.index') }}" class="small">Kelola</a></div>
                @forelse($myUpcoming as $e)
                    <div class="agenda-item">
                        <div class="t">{{ $e->title }}</div>
                        <div class="m">{{ $e->event_date->format('d/m/Y') }}, {{ $e->time_range }} &middot; {{ $e->room->name }}</div>
                    </div>
                @empty
                    <div class="empty">Belum ada agenda yang Anda ajukan.</div>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection
