@extends('layouts.app')

@php($editing = $room->exists)
@section('title', $editing ? 'Ubah ruang' : 'Tambah ruang')

@section('content')
<div class="page-head">
    <h1>{{ $editing ? 'Ubah ruang' : 'Tambah ruang' }}</h1>
    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel">
            <form class="panel-body" method="POST" action="{{ $editing ? route('rooms.update', $room) : route('rooms.store') }}">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nama ruang</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $room->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Lokasi</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $room->location) }}" class="form-control @error('location') is-invalid @enderror" placeholder="Contoh: Gedung A lantai 2">
                    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label for="pic_id" class="form-label">PIC ruang</label>
                    <select name="pic_id" id="pic_id" class="form-select @error('pic_id') is-invalid @enderror">
                        <option value="">Belum ditentukan</option>
                        @foreach($pics as $p)
                            <option value="{{ $p->id }}" @selected(old('pic_id', $room->pic_id) == $p->id)>{{ $p->name }} ({{ $p->rooms_count }} ruang)</option>
                        @endforeach
                    </select>
                    @error('pic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Hanya pengguna berperan PIC Ruang yang muncul di sini.</div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
