@extends('layouts.app')

@section('title', 'Lupa password')

@section('content')
<div class="login-wrap">
    <div class="panel">
        <h1 class="fs-5 fw-semibold mb-1">Lupa password</h1>
        <p class="text-secondary mb-3">Masukkan username dan email yang tertaut di akun Anda. Kode OTP akan dikirim ke email tersebut.</p>

        <form action="{{ route('password.otp') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       class="form-control @error('username') is-invalid @enderror" autocomplete="username" required autofocus>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">Kirim kode OTP</button>
            <div class="text-center mt-3"><a href="{{ route('login') }}" class="small">Kembali ke halaman masuk</a></div>
        </form>
    </div>
</div>
@endsection
