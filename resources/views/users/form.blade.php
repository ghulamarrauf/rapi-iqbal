@extends('layouts.app')

@php($editing = $user->exists)
@section('title', $editing ? 'Ubah pengguna' : 'Tambah pengguna')

@section('content')
<div class="page-head">
    <h1>{{ $editing ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel">
            <form class="panel-body" method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nama lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" autocomplete="off"
                           class="form-control @error('email') is-invalid @enderror" placeholder="nama@email.com" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Wajib untuk semua peran. Kode OTP lupa password dikirim ke email ini.</div>
                </div>
                <div class="mb-3">
                    <label for="role_id" class="form-label">Peran</label>
                    <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" inputmode="tel"
                           class="form-control @error('phone') is-invalid @enderror" placeholder="08123456789">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Wajib untuk PIC Ruang, dipakai tombol peringatan WhatsApp dari Waka.</div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" autocomplete="new-password"
                           class="form-control @error('password') is-invalid @enderror" {{ $editing ? '' : 'required' }}>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($editing) <div class="form-text">Kosongkan jika tidak ingin mengganti password.</div> @endif
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
