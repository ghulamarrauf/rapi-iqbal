@extends('layouts.app')

@section('title', 'Peminjaman ruang')

@section('content')
@php($me = auth()->user())
<div class="page-head">
    <div>
        <h1>Peminjaman ruang &amp; agenda</h1>
        <p>{{ $me->isAdmin() ? 'Seluruh pengajuan agenda sekolah.' : 'Pengajuan agenda milik Anda.' }}</p>
    </div>
    <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Ajukan peminjaman</a>
</div>

@if($errors->any())
    <div class="notice notice-err">
        @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
    </div>
@endif

<div class="panel">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Agenda</th>
                    <th>Ruang</th>
                    <th>Tanggal &amp; jam</th>
                    <th>Status</th>
                    @if($me->isAdmin()) <th>Pemohon</th> @endif
                    @if($me->isAdmin())
                        <th class="act-col">Persetujuan</th>
                        <th class="act-col">Pindah ruang</th>
                    @endif
                    <th class="act-col">Lapor kerusakan</th>
                    <th class="act-col">Ubah / Hapus</th>
                </tr>
            </thead>
            <tbody>
            @forelse($events as $e)
                <tr>
                    <td class="num">{{ $events->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold">{{ $e->title }}</div>
                        <span class="cat-tag {{ $e->categoryClass() }}">{{ $e->categoryLabel() }}</span>
                        @if($e->description)
                            <div class="text-secondary small">{{ \Illuminate\Support\Str::limit($e->description, 70) }}</div>
                        @endif
                        @if($e->move_reason)
                            <div class="small mt-1">
                                <span class="tag tag-moved">Dipindah</span>
                                dari {{ $e->previousRoom->name ?? '-' }} &mdash; <span class="text-secondary">{{ $e->move_reason }}</span>
                            </div>
                        @endif
                    </td>
                    <td>{{ $e->room->name }}</td>
                    <td>
                        {{ $e->event_date->format('d/m/Y') }}
                        <div class="text-secondary small">{{ $e->time_range }}</div>
                    </td>
                    <td>
                        <span class="tag {{ $e->badgeClass() }}">{{ $e->status }}</span>
                        @if($e->status === \App\Models\Event::DITOLAK && $e->reject_reason)
                            <div class="small mt-1 text-danger">Alasan: {{ $e->reject_reason }}</div>
                        @endif
                    </td>
                    @if($me->isAdmin()) <td>{{ $e->user->name }}</td> @endif
                    @if($me->isAdmin())
                        {{-- Kolom 1: persetujuan --}}
                        <td class="act-col">
                            <div class="act-group">
                                @if($e->status !== \App\Models\Event::DISETUJUI)
                                    <form action="{{ route('events.approve', $e) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="icon-btn ok" title="Setujui" aria-label="Setujui"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                @endif
                                @if($e->status !== \App\Models\Event::DITOLAK)
                                    <button type="button" class="icon-btn danger js-reject" title="Tolak" aria-label="Tolak"
                                            data-action="{{ route('events.reject', $e) }}"
                                            data-title="{{ $e->title }}"><i class="bi bi-x-lg"></i></button>
                                @endif
                            </div>
                        </td>
                        {{-- Kolom 2: pindah ruang --}}
                        <td class="act-col">
                            <div class="act-group">
                                <button type="button" class="icon-btn js-move" title="Pindah ruang" aria-label="Pindah ruang"
                                        data-action="{{ route('events.move', $e) }}"
                                        data-title="{{ $e->title }}"
                                        data-room="{{ $e->room_id }}"
                                        data-room-name="{{ $e->room->name }}"><i class="bi bi-arrow-left-right"></i></button>
                            </div>
                        </td>
                    @endif
                    {{-- Kolom 3: lapor kerusakan (hanya peminjaman milik sendiri yang sudah disetujui) --}}
                    <td class="act-col">
                        <div class="act-group">
                            @if(($me->isGuru() || $me->isAdmin()) && (int) $e->user_id === (int) $me->id && $e->isApproved())
                                <a href="{{ route('complaints.create', ['event_id' => $e->id]) }}" class="icon-btn warn" title="Lapor kerusakan" aria-label="Lapor kerusakan"><i class="bi bi-exclamation-triangle"></i></a>
                            @else
                                <span class="text-secondary small">&ndash;</span>
                            @endif
                        </div>
                    </td>
                    {{-- Kolom 4: ubah, hapus --}}
                    <td class="act-col">
                        <div class="act-group">
                            <a href="{{ route('events.edit', $e) }}" class="icon-btn" title="Ubah" aria-label="Ubah"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('events.destroy', $e) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus agenda &quot;{{ addslashes($e->title) }}&quot;?')">
                                @csrf @method('DELETE')
                                <button class="icon-btn danger" title="Hapus" aria-label="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $me->isAdmin() ? 10 : 7 }}" class="empty">Belum ada pengajuan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
        <div class="pager">{{ $events->links() }}</div>
    @endif
</div>

@if($me->isAdmin())
<div class="modal fade" id="moveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="moveForm">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-semibold">Pindahkan ruang agenda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong id="moveTitle"></strong><br><span class="text-secondary">Ruang saat ini: <span id="moveCurrent"></span></span></p>
                <div class="mb-3">
                    <label class="form-label" for="moveRoom">Ruang baru</label>
                    <select name="room_id" id="moveRoom" class="form-select" required>
                        <option value="">Pilih ruang</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="moveReason">Alasan pemindahan</label>
                    <textarea name="move_reason" id="moveReason" rows="3" class="form-control" required minlength="5" maxlength="500"
                              placeholder="Contoh: Aula dipakai untuk kegiatan mendadak dari yayasan"></textarea>
                    <div class="form-text">Alasan ini akan dikirim sebagai notifikasi ke pemilik agenda.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Pindahkan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="rejectForm">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-semibold">Tolak pengajuan peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong id="rejectTitle"></strong></p>
                <label class="form-label" for="rejectReason">Alasan penolakan</label>
                <textarea name="reject_reason" id="rejectReason" rows="3" class="form-control" required minlength="5" maxlength="500"
                          placeholder="Contoh: Ruang dipakai untuk ujian pada jam tersebut"></textarea>
                <div class="form-text">Alasan ini akan dikirim sebagai notifikasi ke pemohon.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak pengajuan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        var rejectForm = document.getElementById('rejectForm');
        document.querySelectorAll('.js-reject').forEach(function (btn) {
            btn.addEventListener('click', function () {
                rejectForm.action = btn.dataset.action;
                document.getElementById('rejectTitle').textContent = btn.dataset.title;
                document.getElementById('rejectReason').value = '';
                rejectModal.show();
            });
        });

        var modalEl = document.getElementById('moveModal');
        var modal = new bootstrap.Modal(modalEl);
        var form = document.getElementById('moveForm');
        var select = document.getElementById('moveRoom');

        document.querySelectorAll('.js-move').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('moveTitle').textContent = btn.dataset.title;
                document.getElementById('moveCurrent').textContent = btn.dataset.roomName;
                document.getElementById('moveReason').value = '';
                select.value = '';
                Array.prototype.forEach.call(select.options, function (o) {
                    o.disabled = (o.value === btn.dataset.room);
                });
                modal.show();
            });
        });
    })();
</script>
@endpush
@endif
@endsection
