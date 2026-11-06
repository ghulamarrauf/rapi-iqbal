<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - RAPI-Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/rapi.css') }}" rel="stylesheet">
</head>
<body>

@auth
    @php($me = auth()->user())
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <strong>RAPI-Sekolah</strong>
            <small>Agenda, ruang, dan fasilitas</small>
        </a>

        <div class="nav-group">Menu</div>
        <ul class="side-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="{{ route('calendar') }}" class="{{ request()->routeIs('calendar') ? 'active' : '' }}"><i class="bi bi-calendar3"></i> Kalender agenda</a></li>
            <li><a href="{{ route('rooms.search') }}" class="{{ request()->routeIs('rooms.search') ? 'active' : '' }}"><i class="bi bi-search"></i> Cari ruangan</a></li>
            @if($me->isAdmin() || $me->isGuru())
                <li><a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}"><i class="bi bi-door-open"></i> Peminjaman ruang</a></li>
            @endif
            <li>
                <a href="{{ route('complaints.index') }}" class="{{ request()->routeIs('complaints.*') ? 'active' : '' }}">
                    <i class="bi bi-tools"></i> {{ $me->isPic() ? 'Pengaduan ruangan' : 'Pengaduan fasilitas' }}
                </a>
            </li>
        </ul>

        @if($me->isAdmin())
            <div class="nav-group">Master data</div>
            <ul class="side-nav">
                <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Pengguna</a></li>
                <li><a href="{{ route('rooms.index') }}" class="{{ request()->routeIs('rooms.*') ? 'active' : '' }}"><i class="bi bi-building"></i> Ruang &amp; PIC</a></li>
                <li><a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="bi bi-shield-check"></i> Peran</a></li>
            </ul>

            <div class="nav-group">Laporan</div>
            <ul class="side-nav">
                <li><a href="{{ route('reports.index', ['mode' => 'harian']) }}" class="{{ request()->routeIs('reports.*') && request('mode') !== 'bulanan' ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i> Laporan harian</a></li>
                <li><a href="{{ route('reports.index', ['mode' => 'bulanan']) }}" class="{{ request()->routeIs('reports.*') && request('mode') === 'bulanan' ? 'active' : '' }}"><i class="bi bi-calendar-month"></i> Laporan bulanan</a></li>
            </ul>
        @endif

        <div class="side-user">
            <span class="avatar">{{ $me->initials() }}</span>
            <div class="who">
                <a href="{{ route('profile.edit') }}">{{ $me->name }}</a>
                <small>{{ $me->role->name ?? '' }}</small>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Keluar"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar d-lg-none">
            <button type="button" id="sidebarToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button>
            <strong>RAPI-Sekolah</strong>
        </header>
        <main class="content">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
@else
    <header class="guest-bar">
        <div class="inner">
            <a href="{{ route('calendar') }}" class="brand"><strong>RAPI-Sekolah</strong></a>
            @if(! request()->routeIs('login'))
                <a href="{{ route('login') }}" class="btn btn-sm btn-light">Masuk</a>
            @endif
        </div>
    </header>
    <main class="content">
        @include('partials.flash')
        @yield('content')
    </main>
@endauth

{{-- Pop-up pemberitahuan: ruang dipindahkan, pengajuan disetujui/ditolak --}}
@auth
    @if($unreadNotifications->isNotEmpty())
        <div class="modal fade" id="notifModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-semibold">Pemberitahuan</h5>
                    </div>
                    <div class="modal-body">
                        @foreach($unreadNotifications as $n)
                            <p class="{{ $loop->last ? 'mb-0' : 'pb-2 mb-2 border-bottom' }}">{{ $n->message }}</p>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('notifications.read') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Saya sudah membaca</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var side = document.getElementById('sidebar');
        var back = document.getElementById('sidebarBackdrop');
        var btn = document.getElementById('sidebarToggle');
        if (side && btn) {
            var toggle = function (open) {
                side.classList.toggle('open', open);
                back.classList.toggle('show', open);
            };
            btn.addEventListener('click', function () { toggle(!side.classList.contains('open')); });
            back.addEventListener('click', function () { toggle(false); });
        }
        var modal = document.getElementById('notifModal');
        if (modal) { new bootstrap.Modal(modal).show(); }
    })();
</script>
@stack('scripts')
</body>
</html>
