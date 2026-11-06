@extends('layouts.app')

@section('title', 'Detail pengaduan')

@section('content')
@php($me = auth()->user())
<div class="page-head">
    <div>
        <h1>Pengaduan #{{ $complaint->id }}</h1>
        <p>{{ $complaint->room->name }} &middot; dilaporkan {{ $complaint->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-head">
                <h2>Rincian laporan</h2>
                <span class="tag {{ $complaint->badgeClass() }}">{{ $complaint->status_repair }}</span>
            </div>
            <div class="panel-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Pelapor</dt>
                    <dd class="col-sm-8">{{ $complaint->reporter->name }}</dd>
                    <dt class="col-sm-4">Ruang</dt>
                    <dd class="col-sm-8">{{ $complaint->room->name }}{{ $complaint->room->location ? ' ('.$complaint->room->location.')' : '' }}</dd>
                    <dt class="col-sm-4">PIC ruang</dt>
                    <dd class="col-sm-8">{{ $complaint->picName() }}</dd>
                    @if($complaint->event)
                        <dt class="col-sm-4">Saat peminjaman</dt>
                        <dd class="col-sm-8">{{ $complaint->event->title }} &middot; {{ $complaint->event->event_date->format('d/m/Y') }}, {{ $complaint->event->time_range }}</dd>
                    @endif
                    <dt class="col-sm-4">Uraian</dt>
                    <dd class="col-sm-8">{!! nl2br(e($complaint->description)) !!}</dd>
                    <dt class="col-sm-4">Foto bukti</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($complaint->photo_path)
                            <a href="{{ route('files.show', ['path' => $complaint->photo_path]) }}" target="_blank">
                                <img src="{{ route('files.show', ['path' => $complaint->photo_path]) }}" class="proof" alt="Foto bukti kerusakan">
                            </a>
                        @else
                            <span class="text-secondary">Tidak ada foto.</span>
                        @endif
                    </dd>
                    @if($complaint->repair_photo_path)
                        <dt class="col-sm-4 mt-3">Foto setelah diperbaiki</dt>
                        <dd class="col-sm-8 mt-3 mb-0">
                            <a href="{{ route('files.show', ['path' => $complaint->repair_photo_path]) }}" target="_blank">
                                <img src="{{ route('files.show', ['path' => $complaint->repair_photo_path]) }}" class="proof" alt="Foto hasil perbaikan">
                            </a>
                            @if($complaint->repaired_at)
                                <div class="text-secondary small mt-1">Selesai {{ $complaint->repaired_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if($canUpdate && $complaint->status_repair === \App\Models\Complaint::SELESAI)
            <div class="panel">
                <div class="panel-head"><h2>Status penanganan</h2><span class="tag {{ $complaint->badgeClass() }}">{{ $complaint->status_repair }}</span></div>
                <div class="panel-body text-secondary">Laporan ini sudah selesai diperbaiki dan statusnya tidak dapat diubah lagi.</div>
            </div>
        @elseif($canUpdate)
            <div class="panel">
                <div class="panel-head"><h2>Perbarui status penanganan</h2></div>
                <form class="panel-body" method="POST" action="{{ route('complaints.updateStatus', $complaint) }}" enctype="multipart/form-data">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        @foreach(\App\Models\Complaint::STATUSES as $st)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="status_repair" id="st{{ $loop->index }}"
                                       value="{{ $st }}" @checked(old('status_repair', $complaint->status_repair) === $st)>
                                <label class="form-check-label" for="st{{ $loop->index }}">{{ $st }}</label>
                            </div>
                        @endforeach
                        @error('status_repair') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 d-none" id="repairPhotoBox">
                        <label for="repair_photo" class="form-label">Foto hasil perbaikan</label>
                        <input type="file" name="repair_photo" id="repair_photo" accept="image/*" capture="environment"
                               class="form-control @error('repair_photo') is-invalid @enderror">
                        @error('repair_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Wajib diunggah sebagai bukti sebelum menyimpan status Sudah Diperbaiki.
                            @if($complaint->repair_photo_path) Foto sebelumnya sudah ada; unggah lagi hanya jika ingin menggantinya. @endif
                        </div>
                        <img id="repairPreview" class="proof mt-2 d-none" alt="Pratinjau foto perbaikan">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan status</button>
                </form>
            </div>
        @endif

        @if($me->isAdmin())
            <div class="panel">
                <div class="panel-head"><h2>Peringatan ke PIC</h2></div>
                <div class="panel-body">
                    @if($complaint->isOverdue())
                        <p class="mb-3">Laporan ini belum diperbaiki lebih dari {{ \App\Models\Complaint::ESCALATION_DAYS }} hari.</p>
                        @if($url = $complaint->whatsappUrl())
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-wa"><i class="bi bi-whatsapp"></i> Kirim peringatan lewat WhatsApp</a>
                        @else
                            <p class="text-danger mb-0">PIC ruang ini belum punya nomor WhatsApp. Lengkapi di Master data.</p>
                        @endif
                    @else
                        <p class="text-secondary mb-0">Tombol peringatan aktif jika status masih Belum Diperbaiki setelah {{ \App\Models\Complaint::ESCALATION_DAYS }} hari.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var box = document.getElementById('repairPhotoBox');
        var file = document.getElementById('repair_photo');
        var preview = document.getElementById('repairPreview');
        if (!box) return;
        var done = {{ \Illuminate\Support\Js::from(\App\Models\Complaint::SELESAI) }};
        var needed = {{ $complaint->repair_photo_path ? 'false' : 'true' }};

        function sync() {
            var checked = document.querySelector('input[name="status_repair"]:checked');
            var isDone = checked && checked.value === done;
            box.classList.toggle('d-none', !isDone);
            file.required = isDone && needed;
        }
        document.querySelectorAll('input[name="status_repair"]').forEach(function (r) { r.addEventListener('change', sync); });
        file.addEventListener('change', function () {
            var f = file.files[0];
            if (!f) { preview.classList.add('d-none'); return; }
            preview.src = URL.createObjectURL(f);
            preview.classList.remove('d-none');
        });
        sync();
    })();
</script>
@endpush
