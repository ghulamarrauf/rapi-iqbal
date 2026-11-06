@extends('layouts.app')

@section('title', 'Verifikasi OTP')

@section('content')
<div class="login-wrap">
    <div class="panel">
        <h1 class="fs-5 fw-semibold mb-1">Masukkan kode OTP</h1>
        <p class="text-secondary mb-3">Kode 6 angka dikirim ke email Anda dan berlaku {{ $ttl }} menit. Belum masuk? Cek folder spam atau hubungi Waka/Admin.</p>

        <form action="{{ route('password.reset') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="otp" class="form-label">Kode OTP</label>
                <input type="text" id="otp" name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code"
                       class="form-control @error('otp') is-invalid @enderror" required autofocus>
                @error('otp') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password baru</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Ulangi password baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Simpan password baru</button>
            <div class="text-center mt-3"><a href="{{ route('password.request') }}" class="small">Kirim ulang kode / ganti email</a></div>
        </form>
    </div>
</div>
@endsection
