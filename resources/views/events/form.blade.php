@extends('layouts.app')

@php($editing = $event->exists)
@section('title', $editing ? 'Ubah agenda' : 'Ajukan peminjaman')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $editing ? 'Ubah agenda' : 'Ajukan peminjaman ruang' }}</h1>
        <p>Sistem menolak otomatis jika ruang dan jamnya bertabrakan dengan agenda lain.</p>
    </div>
    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@error('conflict')
    <div class="notice notice-err">{{ $message }}</div>
@enderror

<div class="row">
    <div class="col-lg-8">
        <div class="panel">
            <form class="panel-body" method="POST" id="eventForm"
                  action="{{ $editing ? route('events.update', $event) : route('events.store') }}">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="mb-3">
                    <label for="title" class="form-label">Nama agenda</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}"
                           class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Rapat panitia class meeting" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="room_id" class="form-label">Ruang</label>
                    <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">Pilih ruang</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" @selected(old('room_id', $event->room_id) == $r->id)>
                                {{ $r->name }}{{ $r->location ? ' - '.$r->location : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="event_date" class="form-label">Tanggal</label>
                        <input type="date" name="event_date" id="event_date" min="{{ \App\Support\JamPelajaran::today() }}"
                               value="{{ old('event_date', $event->event_date?->toDateString()) }}"
                               class="form-control @error('event_date') is-invalid @enderror" required>
                        @error('event_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="start_period" class="form-label">Jam ke- mulai</label>
                        <select name="start_period" id="start_period" class="form-select @error('start_period') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            @foreach(\App\Support\JamPelajaran::all() as $no => $jam)
                                <option value="{{ $no }}" @selected(old('start_period', $event->start_period) == $no)>{{ \App\Support\JamPelajaran::label($no) }}</option>
                            @endforeach
                        </select>
                        @error('start_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="end_period" class="form-label">Jam ke- selesai</label>
                        <select name="end_period" id="end_period" class="form-select @error('end_period') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            @foreach(\App\Support\JamPelajaran::all() as $no => $jam)
                                <option value="{{ $no }}" @selected(old('end_period', $event->end_period) == $no)>{{ \App\Support\JamPelajaran::label($no) }}</option>
                            @endforeach
                        </select>
                        @error('end_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ketersediaan ruang pada tanggal tersebut</label>
                    <div class="slots" id="slots">Pilih ruang dan tanggal untuk melihat jam pelajaran yang sudah terpakai.</div>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Jenis agenda</label>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                        @foreach(\App\Models\Event::KATEGORI as $k => $label)
                            @continue(in_array($k, \App\Models\Event::KATEGORI_ADMIN, true) && ! auth()->user()->isAdmin())
                            <option value="{{ $k }}" @selected(old('category', $event->category ?: 'Peminjaman') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Menentukan warna agenda di kalender. Event sekolah dibuat oleh Waka/Admin.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Keterangan <span class="text-secondary fw-normal">(opsional)</span></label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">{{ $editing ? 'Simpan perubahan' : 'Ajukan' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var room = document.getElementById('room_id');
        var date = document.getElementById('event_date');
        var start = document.getElementById('start_period');
        var end = document.getElementById('end_period');
        var periods = @json(\App\Support\JamPelajaran::all());
        var box = document.getElementById('slots');
        var submit = document.getElementById('submitBtn');
        var except = {{ $editing ? (int) $event->id : 'null' }};
        var slots = [];
        // Saat mengubah agenda lama, tanggal & jam mulai asli tidak dikunci walau sudah lewat
        var origDate = @json($editing ? $event->event_date?->toDateString() : null);
        var origStart = @json($editing ? (string) $event->start_period : null);

        // Jam pelajaran yang sudah berakhir (hari ini, zona WIB) dinonaktifkan agar tidak bisa dipilih
        function applyPast() {
            var now = new Date();
            var today = now.toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' });
            var hm = now.toLocaleTimeString('en-GB', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', hour12: false });
            var nowMin = minutes(hm);
            var unchanged = origDate && date.value === origDate && start.value === origStart;
            [start, end].forEach(function (sel) {
                Array.prototype.forEach.call(sel.options, function (opt) {
                    if (!opt.value || !periods[opt.value]) return;
                    var over = !unchanged && date.value === today && minutes(periods[opt.value][1]) <= nowMin;
                    opt.disabled = over;
                    if (over && sel.value === opt.value) sel.value = '';
                });
            });
        }

        function minutes(t) { var p = t.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10); }

        function clash() {
            if (!start.value || !end.value) return null;
            if (!periods[start.value] || !periods[end.value]) return null;
            var s = minutes(periods[start.value][0]), e = minutes(periods[end.value][1]);
            for (var i = 0; i < slots.length; i++) {
                if (s < minutes(slots[i].end) && e > minutes(slots[i].start)) return slots[i];
            }
            return null;
        }

        function render() {
            box.textContent = '';
            box.classList.remove('warn');
            submit.disabled = false;

            if (!room.value || !date.value) {
                box.textContent = 'Pilih ruang dan tanggal untuk melihat jam yang sudah terpakai.';
                return;
            }
            if (!slots.length) {
                box.textContent = 'Belum ada agenda di ruang ini pada tanggal tersebut.';
                return;
            }
            slots.forEach(function (s) {
                var row = document.createElement('div');
                row.textContent = s.label + ' (' + s.start + ' - ' + s.end + ')  ' + s.title + ' (' + s.by + ')';
                box.appendChild(row);
            });
            var c = clash();
            if (c) {
                box.classList.add('warn');
                var warn = document.createElement('div');
                warn.className = 'fw-semibold mt-2';
                warn.textContent = 'Jam pelajaran yang Anda pilih bertabrakan dengan ' + c.label + '. Ubah jam atau ruang.';
                box.appendChild(warn);
                submit.disabled = true;
            }
        }

        function load() {
            if (!room.value || !date.value) { slots = []; render(); return; }
            var q = new URLSearchParams({ room_id: room.value, date: date.value });
            if (except) q.append('except', except);
            fetch('{{ route('events.availability') }}?' + q.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(function (data) { slots = data; render(); })
                .catch(function () { slots = []; render(); });
        }

        room.addEventListener('change', load);
        date.addEventListener('change', function () { applyPast(); load(); });
        start.addEventListener('change', render);
        end.addEventListener('change', render);
        applyPast();
        load();
    })();
</script>
@endpush
