<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomSearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
| role_id: 1 = Waka/Admin, 2 = Guru/Organisasi, 3 = PIC Ruang
| Siswa/Tamu tidak login; mereka hanya membuka kalender publik.
*/

// Halaman publik (read-only)
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('calendar'));
Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar');

// Satu pintu login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Lupa password: kode OTP dikirim ke email yang tertaut di akun
    Route::get('/lupa-password', [AuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('password.otp');
    Route::get('/lupa-password/verifikasi', [AuthController::class, 'showVerifyForm'])->name('password.verify');
    Route::post('/lupa-password/verifikasi', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:10,1')
        ->name('password.reset');
});

Route::middleware(['auth', 'email.linked'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/cari-ruang', [RoomSearchController::class, 'index'])->name('rooms.search');

    // Foto pengaduan (disajikan lewat Laravel agar tidak bergantung pada symlink public/storage)
    Route::get('/berkas/{path}', [FileController::class, 'show'])
        ->where('path', '(complaints|repairs)/[A-Za-z0-9._-]+')
        ->name('files.show');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/notifikasi/baca', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Pengajuan peminjaman ruang & agenda: Admin dan Guru/Organisasi
    Route::middleware('role:1,2')->group(function () {
        Route::get('/events/availability', [EventController::class, 'availability'])->name('events.availability');
        Route::resource('events', EventController::class)->except('show');
    });

    // Pemindahan ruang: khusus Waka/Admin
    Route::middleware('role:1')->group(function () {
        Route::patch('/events/{event}/move', [EventController::class, 'move'])->name('events.move');
        Route::patch('/events/{event}/approve', [EventController::class, 'approve'])->name('events.approve');
        Route::patch('/events/{event}/reject', [EventController::class, 'reject'])->name('events.reject');

        // Laporan harian & bulanan (peminjaman ruang + kerusakan)
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/unduh/{type}', [ReportController::class, 'download'])
            ->whereIn('type', ['peminjaman', 'kerusakan'])
            ->name('reports.download');

        Route::resource('users', UserController::class)->except('show');
        Route::resource('rooms', RoomController::class)->except('show');
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    // Pengaduan kerusakan fasilitas
    // Guru/Organisasi dan Waka/Admin dapat membuat pengaduan kerusakan
    Route::middleware('role:1,2')->group(function () {
        Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    });
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])
        ->middleware('role:3')
        ->name('complaints.updateStatus');
});
