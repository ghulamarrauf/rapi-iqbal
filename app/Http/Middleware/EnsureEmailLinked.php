<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Semua peran wajib menautkan email. Akun yang belum punya email diarahkan ke halaman profil
 * sampai email diisi (hanya profil dan logout yang boleh dibuka).
 */
class EnsureEmailLinked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && blank($user->email) && ! $request->routeIs('profile.edit', 'profile.update', 'logout')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Tautkan email Anda terlebih dahulu. Email dipakai untuk menerima kode OTP saat lupa password.');
        }

        return $next($request);
    }
}
