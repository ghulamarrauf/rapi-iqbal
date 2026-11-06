<?php

namespace App\Http\Controllers;

use App\Mail\PasswordOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['username' => 'Username atau password salah.'])
            ->onlyInput('username');
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Langkah 1: pengguna memasukkan email yang tertaut di akunnya. Sistem membuat OTP 6 digit
     * (disimpan sebagai hash) dan mengirimnya ke email tersebut. Respons selalu sama agar tidak
     * membocorkan email mana yang terdaftar.
     */
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'email'    => ['required', 'email'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
        ]);
        $username = trim($data['username']);
        $email    = mb_strtolower(trim($data['email']));

        // Username dan email harus milik akun yang sama
        $user = User::where('username', $username)->where('email', $email)->first();

        if ($user) {
            $recent = PasswordOtp::where('user_id', $user->id)
                ->where('created_at', '>', now()->subSeconds(60))
                ->exists();

            // Jangan kirim ulang bila kode baru saja dikirim (cegah spam ke email pengguna)
            if (! $recent) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                PasswordOtp::where('user_id', $user->id)->delete();
                PasswordOtp::create([
                    'user_id'    => $user->id,
                    'code'       => Hash::make($code),
                    'expires_at' => now()->addMinutes(PasswordOtp::TTL_MINUTES),
                ]);

                try {
                    Mail::to($user->email)->send(new PasswordOtpMail($user->name, $code, PasswordOtp::TTL_MINUTES));
                } catch (\Throwable $e) {
                    Log::warning('[OTP] Gagal mengirim email: '.$e->getMessage());
                    PasswordOtp::where('user_id', $user->id)->delete();

                    return back()
                        ->withErrors(['email' => 'Kode OTP gagal dikirim. Coba lagi beberapa saat atau hubungi Waka/Admin.'
                            // Saat APP_DEBUG=true, tampilkan penyebab asli agar mudah diperbaiki
                            .(config('app.debug') ? ' [Detail: '.$e->getMessage().']' : '')])
                        ->onlyInput('username', 'email');
                }
            }
        }

        $request->session()->put(['reset_username' => $username, 'reset_email' => $email]);

        return redirect()->route('password.verify')
            ->with('success', 'Jika username dan email cocok, kode OTP sudah dikirim. Periksa kotak masuk (dan folder spam).');
    }

    public function showVerifyForm(Request $request)
    {
        if (! $request->session()->has('reset_email') || ! $request->session()->has('reset_username')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', ['ttl' => PasswordOtp::TTL_MINUTES]);
    }

    /** Langkah 2: cocokkan OTP, lalu simpan password baru. */
    public function resetPassword(Request $request)
    {
        $email    = $request->session()->get('reset_email');
        $username = $request->session()->get('reset_username');
        if (! $email || ! $username) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'otp'      => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:3', 'confirmed'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP terdiri dari 6 angka.',
        ]);

        $user = User::where('username', $username)->where('email', $email)->first();
        $otp  = $user ? PasswordOtp::where('user_id', $user->id)->latest('id')->first() : null;

        if (! $otp || ! $otp->isUsable()) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa. Minta kode baru.']);
        }

        if (! Hash::check($data['otp'], $otp->code)) {
            $otp->increment('attempts');
            $left = max(0, PasswordOtp::MAX_ATTEMPTS - $otp->attempts);

            return back()->withErrors(['otp' => $left > 0
                ? "Kode OTP salah. Sisa percobaan: {$left}."
                : 'Kode OTP salah dan batas percobaan habis. Minta kode baru.']);
        }

        $user->password = $data['password']; // di-hash otomatis oleh cast
        $user->setRememberToken(Str::random(60));
        $user->save();

        PasswordOtp::where('user_id', $user->id)->delete();
        $request->session()->forget(['reset_username', 'reset_email']);

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk dengan password baru.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('calendar')->with('success', 'Anda sudah keluar.');
    }
}
