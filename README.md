# RAPI-Sekolah

Rencana Agenda dan Penjadwalan Terintegrasi: agenda sekolah, peminjaman ruang,
dan pelaporan kerusakan fasilitas. Laravel + MySQL, tampilan mobile-first.

## Menjalankan

```bash
composer install
cp .env.example .env          # lalu sesuaikan DB_* bila perlu
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link      # supaya foto bukti kerusakan bisa tampil
php artisan serve
```

Buat database `db_rapi_sekolah` di MySQL/MariaDB terlebih dahulu.
Ekstensi PHP `gd` sebaiknya aktif (untuk kompres foto); tanpa itu foto disimpan apa adanya.

## Akun contoh (password semuanya `123`)

| Username | Peran              |
|----------|--------------------|
| admin    | Waka / Admin       |
| guru     | Guru / Organisasi  |
| osis     | Guru / Organisasi  |
| pic      | PIC Ruang (3 ruang: Lab Komputer 1, Lab Komputer 2, Bengkel Jaringan) |
| pic2     | PIC Ruang (2 ruang: Aula, Perpustakaan) |

Siswa/tamu tidak perlu akun: buka `/kalender`.

## Hak akses

| Fitur                                   | Waka/Admin | PIC | Guru/Org | Siswa/Tamu |
|-----------------------------------------|:---:|:---:|:---:|:---:|
| Login & profil                          | ya  | ya  | ya  | -   |
| Kalender agenda (hanya lihat)           | ya  | ya  | ya  | ya  |
| Ajukan peminjaman ruang & agenda        | ya  | -   | ya  | -   |
| Pengaduan kerusakan + foto              | -   | -   | ya  | -   |
| Ubah status perbaikan (ruang miliknya)  | -   | ya  | -   | -   |
| Peringatan WhatsApp ke PIC              | ya  | -   | -   | -   |
| Pindah ruang + alasan + notifikasi      | ya  | -   | -   | -   |
| Master pengguna, ruang, peran, PIC      | ya  | -   | -   | -   |

## Fitur tambahan (persetujuan, notifikasi, laporan)

- **Persetujuan peminjaman**: pengajuan Guru/Organisasi berstatus `Menunggu`; Waka menyetujui atau
  menolak dari halaman Peminjaman ruang. **Penolakan wajib disertai alasan** (min. 5 karakter).
  Pengajuan Waka sendiri langsung `Disetujui`. Hanya agenda `Disetujui` tampil di kalender &
  dashboard; pengajuan `Ditolak` tidak memblokir ruang. Perubahan oleh pemohon mengembalikan status ke `Menunggu`.
- **Notifikasi**: pemohon mendapat pesan sukses saat mengirim pengajuan, serta pop-up notifikasi
  ketika pengajuan disetujui (berhasil) atau ditolak (beserta alasan). Bentrok jadwal tetap menampilkan pesan gagal.
- **Lapor kerusakan dari peminjaman**: tombol ikon peringatan di baris peminjaman yang `Disetujui`
  (Guru/Organisasi). Form terisi otomatis: ruang, pelapor, dan PIC ruang (disimpan di `complaints.pic_id`).
- **Laporan harian & bulanan** (Waka/Admin): menu *Laporan harian/bulanan* berisi rekap peminjaman ruang
  dan kerusakan, bisa dicetak dan diunduh CSV.

- **Foto bukti perbaikan**: saat PIC memilih status *Sudah Diperbaiki*, kolom foto muncul dan **wajib diisi**
  (disimpan di `complaints.repair_photo_path`, ikut dikompres). Foto tampil di detail pengaduan.
- **Email wajib & lupa password via OTP**: semua peran harus menautkan email (diisi Waka saat membuat akun
  atau oleh pengguna di *Profil*). Akun lama tanpa email diarahkan ke Profil sampai mengisinya. Lupa password:
  masukkan email -> kode OTP 6 digit dikirim ke email (di-hash di `password_otps`, berlaku 5 menit, maks. 5 kali salah)
  -> isi OTP + password baru. Atur `MAIL_*` di `.env` untuk pengiriman nyata (lihat `.env.example`);
  dengan `MAIL_MAILER=log` email hanya tercatat di `storage/logs/laravel.log`.
- **Cari ruangan**: menu *Cari ruangan* (semua pengguna login) mencari nama/lokasi/PIC dan menampilkan
  ketersediaan per tanggal; halaman *Ruang & PIC* (Waka) juga punya kotak pencarian.

Folder `database/sql/` berisi dua file siap impor lewat phpMyAdmin (tanpa `php artisan migrate`):
`db_rapi_sekolah_lengkap.sql` (impor ke database kosong, data terakhir ikut terbawa) dan
`update_dari_dump_terakhir.sql` (untuk database yang masih memakai struktur dump terakhir).

Setelah memperbarui kode: `php artisan migrate` (data lama tetap aman; agenda lama otomatis `Disetujui`).

## Aturan bisnis

- **Anti-tabrakan**: `Event::scopeOverlapping()` menolak pengajuan pada ruang + tanggal yang
  sama bila `mulai_lama < selesai_baru` dan `selesai_lama > mulai_baru`. Pengecekan berjalan
  di backend dalam transaksi (`lockForUpdate`). Form juga menampilkan jam yang sudah terpakai
  dan mengunci tombol kirim bila jam bentrok.
- **Delegasi PIC**: `rooms.pic_id` merujuk `users.id`. PIC hanya melihat/mengubah laporan di
  ruang yang `pic_id`-nya dirinya; akses lain mendapat 403.
- **Status fasilitas**: enum `Belum Diperbaiki`, `Menunggu Spare Part`, `Sudah Diperbaiki`.
- **Eskalasi WhatsApp**: tombol hijau muncul untuk Waka bila laporan masih `Belum Diperbaiki`
  lebih dari `Complaint::ESCALATION_DAYS` (3 hari), mengarah ke nomor PIC ruang (`wa.me`).
- **Overriding**: Waka memindah agenda ke ruang lain dengan alasan wajib. Alasan disimpan di
  `events.move_reason`, lalu sistem membuat notifikasi untuk pemilik agenda yang tampil sebagai
  pop-up saat ia membuka aplikasi.

## Skema tabel

`roles`, `users` (+`phone`), `rooms` (`pic_id`), `events` (`room_id`, `start_time`, `end_time`,
`move_reason`, `moved_from_room_id`, `status`, `reject_reason`, `reviewed_by`, `reviewed_at`), `complaints` (`room_id`, `reporter_id`, `event_id`, `pic_id`, `photo_path`,
`status_repair`), `room_notifications`, `password_otps`.
