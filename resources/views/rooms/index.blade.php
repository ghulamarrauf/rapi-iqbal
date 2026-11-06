@extends('layouts.app')

@section('title', 'Ruang & PIC')

@section('content')
<div class="page-head">
    <div>
        <h1>Ruang &amp; penugasan PIC</h1>
        <p>Satu PIC dapat memegang beberapa ruang. PIC hanya melihat dan menangani laporan pada ruangnya.</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah ruang</a>
</div>

<div class="panel">
    <form class="table-tools" method="GET" action="{{ route('rooms.index') }}">
        <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" style="max-width: 280px"
               placeholder="Cari nama ruang, lokasi, atau PIC">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Cari</button>
        @if($q !== '') <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a> @endif
    </form>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Ruang</th><th>PIC</th><th>Laporan</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($rooms as $r)
                <tr>
                    <td class="num">{{ $rooms->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold">{{ $r->name }}</div>
                        <div class="text-secondary small">{{ $r->location ?: '-' }}</div>
                    </td>
                    <td>
                        @if($r->pic)
                            <div class="person">
                                <span class="avatar">{{ $r->pic->initials() }}</span>
                                <div>
                                    <div class="nm">{{ $r->pic->name }}</div>
                                    <div class="sub">{{ $r->pic->phone ?: 'tanpa nomor WA' }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-secondary">Belum ditentukan</span>
                        @endif
                    </td>
                    <td>{{ $r->complaints_count }}</td>
                    <td class="actions">
                        <a href="{{ route('rooms.edit', $r) }}" class="icon-btn" title="Ubah"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('rooms.destroy', $r) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus ruang ini? Agenda dan laporan di dalamnya ikut terhapus.')">
                            @csrf @method('DELETE')
                            <button class="icon-btn danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">{{ $q !== '' ? 'Tidak ada ruang yang cocok.' : 'Belum ada ruang.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($rooms->hasPages())
        <div class="pager">{{ $rooms->links() }}</div>
    @endif
</div>
@endsection
