@extends('layouts.app')

@section('title', 'Master pengguna')

@section('content')
<div class="page-head">
    <div>
        <h1>Master pengguna</h1>
        <p>Kelola akun Waka/Admin, guru/organisasi, dan PIC ruang.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah pengguna</a>
</div>

<div class="panel">
    <form class="table-tools" method="GET" action="{{ route('users.index') }}">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="max-width: 240px" placeholder="Cari nama atau username">
        <select name="role" class="form-select form-select-sm" style="max-width: 190px" onchange="this.form.submit()">
            <option value="">Semua peran</option>
            @foreach($roles as $r)
                <option value="{{ $r->id }}" @selected(request('role') == $r->id)>{{ $r->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary btn-sm">Cari</button>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Pengguna</th><th>Peran</th><th>WhatsApp</th><th>Ruang dipegang</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td class="num">{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="person">
                            <span class="avatar">{{ $u->initials() }}</span>
                            <div>
                                <div class="nm">{{ $u->name }}</div>
                                <div class="sub">{{ '@'.$u->username }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="tag tag-role">{{ $u->role->name }}</span></td>
                    <td>{{ $u->phone ?: '-' }}</td>
                    <td>{{ $u->isPic() ? $u->rooms_count.' ruang' : '-' }}</td>
                    <td class="actions">
                        <a href="{{ route('users.edit', $u) }}" class="icon-btn" title="Ubah"><i class="bi bi-pencil"></i></a>
                        @if($u->id !== auth()->id())
                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus pengguna ini? Agenda dan laporan miliknya ikut terhapus.')">
                                @csrf @method('DELETE')
                                <button class="icon-btn danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Tidak ada pengguna yang cocok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="pager">{{ $users->links() }}</div>
    @endif
</div>
@endsection
