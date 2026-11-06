@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="page-head">
    <div>
        <h1>Profil saya</h1>
        <p>{{ $user->role->name }} &middot; {{ '@'.$user->username }}</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel">
            <form class="panel-body" method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Wajib. Dipakai untuk menerima kode OTP saat lupa password.</div>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" inputmode="tel" class="form-control @error('phone') is-invalid @enderror">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <hr>
                <div class="mb-3">
                    <label for="password" class="form-label">Password baru</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Kosongkan jika tidak ingin mengganti.</div>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Ulangi password baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
