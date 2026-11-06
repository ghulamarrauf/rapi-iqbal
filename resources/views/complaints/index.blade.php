@extends('layouts.app')

@section('title', 'Pengaduan fasilitas')

@section('content')
@php($me = auth()->user())
<div class="page-head">
    <div>
        <h1>{{ $me->isPic() ? 'Pengaduan pada ruangan Anda' : 'Pengaduan kerusakan fasilitas' }}</h1>
        <p>
            @if($me->isAdmin()) Semua laporan dari guru, organisasi, dan Waka/Admin.
            @elseif($me->isPic()) Hanya laporan pada ruang yang didelegasikan kepada Anda.
            @else Laporan yang Anda kirim. @endif
        </p>
    </div>
    @if($me->isGuru() || $me->isAdmin())
        <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Buat pengaduan</a>
    @endif
</div>

<div class="panel">
    <form class="table-tools" method="GET" action="{{ route('complaints.index') }}">
        <select name="status" class="form-select form-select-sm" style="max-width: 210px" onchange="this.form.submit()">
            <option value="" hidden @selected(! in_array($filter, \App\Models\Complaint::STATUSES, true))>Filter status</option>
            @foreach(\App\Models\Complaint::STATUSES as $st)
                <option value="{{ $st }}" @selected($filter === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @if(in_array($filter, \App\Models\Complaint::STATUSES, true))
            <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ruang</th>
                    <th>Laporan</th>
                    @unless($me->isGuru()) <th>Pelapor</th> @endunless
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($complaints as $c)
                <tr>
                    <td class="num">{{ $complaints->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold">{{ $c->room->name }}</div>
                        @if($me->isAdmin())
                            <div class="text-secondary small">PIC: {{ $c->picName() }}</div>
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($c->description, 70) }}</td>
                    @unless($me->isGuru()) <td>{{ $c->reporter->name }}</td> @endunless
                    <td class="text-nowrap">{{ $c->created_at->format('d/m/Y') }}</td>
                    <td><span class="tag {{ $c->badgeClass() }}">{{ $c->status_repair }}</span></td>
                    <td class="actions">
                        @if($me->isAdmin() && $c->isOverdue())
                            @if($url = $c->whatsappUrl())
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-wa btn-sm me-1"><i class="bi bi-whatsapp"></i> Ingatkan PIC</a>
                            @else
                                <span class="text-secondary small me-1">PIC belum punya nomor WA</span>
                            @endif
                        @endif
                        <a href="{{ route('complaints.show', $c) }}" class="btn btn-outline-secondary btn-sm">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Belum ada laporan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($complaints->hasPages())
        <div class="pager">{{ $complaints->links() }}</div>
    @endif
</div>
@endsection
