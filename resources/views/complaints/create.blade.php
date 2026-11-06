@extends('layouts.app')

@section('title', 'Buat pengaduan')

@section('content')
<div class="page-head">
    <div>
        <h1>Laporkan kerusakan fasilitas</h1>
        <p>Laporan otomatis diteruskan ke PIC ruangan yang dipilih.</p>
    </div>
    <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="panel">
            <form class="panel-body" method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data">
                @csrf

                @php($me = auth()->user())
                @if($event)
                    {{-- Dibuka dari peminjaman: ruang, pelapor, dan PIC terisi otomatis --}}
                    <input type="hidden" name="room_id" value="{{ $event->room_id }}">
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    <dl class="auto-info mb-3">
                        <dt>Ruang</dt>
                        <dd>{{ $event->room->name }}{{ $event->room->location ? ' ('.$event->room->location.')' : '' }}</dd>
                        <dt>Pelapor</dt>
                        <dd>{{ $me->name }}</dd>
                        <dt>PIC ruang</dt>
                        <dd>{{ $event->room->pic->name ?? 'Belum ditentukan' }}</dd>
                        <dt>Peminjaman</dt>
                        <dd>{{ $event->title }} &middot; {{ $event->event_date->format('d/m/Y') }}, {{ $event->time_range }}</dd>
                    </dl>
                    @error('room_id') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                @else
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Ruang</label>
                        <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                            <option value="">Pilih ruang</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}" data-pic="{{ $r->pic->name ?? 'Belum ditentukan' }}" @selected(old('room_id') == $r->id)>{{ $r->name }}{{ $r->location ? ' - '.$r->location : '' }}</option>
                            @endforeach
                        </select>
                        @error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <dl class="auto-info mb-3">
                        <dt>Pelapor</dt>
                        <dd>{{ $me->name }}</dd>
                        <dt>PIC ruang (otomatis)</dt>
                        <dd id="picName">Pilih ruang terlebih dahulu</dd>
                    </dl>
                @endif

                <div class="mb-3">
                    <label for="description" class="form-label">Apa yang rusak?</label>
                    <textarea name="description" id="description" rows="4" required
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Sebutkan bagian yang rusak dan letaknya">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="photo" class="form-label">Foto bukti</label>
                    {{-- capture="environment" membuka kamera belakang di HP; di komputer tetap memilih file --}}
                    <input type="file" name="photo" id="photo" accept="image/*" capture="environment" required
                           class="form-control @error('photo') is-invalid @enderror">
                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Di ponsel, tombol ini langsung membuka kamera. Foto dikecilkan otomatis saat disimpan.</div>
                    <img id="preview" class="proof mt-2 d-none" alt="Pratinjau foto">
                </div>

                <button type="submit" class="btn btn-primary">Kirim laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var room = document.getElementById('room_id');
        var pic = document.getElementById('picName');
        if (!room || !pic) return;
        function showPic() {
            var opt = room.options[room.selectedIndex];
            pic.textContent = room.value ? opt.dataset.pic : 'Pilih ruang terlebih dahulu';
        }
        room.addEventListener('change', showPic);
        showPic();
    })();
    document.getElementById('photo').addEventListener('change', function (e) {
        var img = document.getElementById('preview');
        var file = e.target.files[0];
        if (!file) { img.classList.add('d-none'); return; }
        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
    });
</script>
@endpush
