@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="login-wrap">
    <div class="panel">
        <h1 class="fs-5 fw-semibold mb-1">Masuk</h1>
        <p class="text-secondary mb-3">Untuk Waka, guru/organisasi, dan PIC ruang. Siswa dan tamu dapat melihat <a href="{{ route('calendar') }}">kalender agenda</a> tanpa masuk.</p>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       class="form-control @error('username') is-invalid @enderror" autocomplete="username" required autofocus>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
                <div class="text-end mt-1"><a href="{{ route('password.request') }}" class="small">Lupa password?</a></div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
    </div>
</div>
@endsection
