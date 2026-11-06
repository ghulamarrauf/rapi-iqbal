<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan id harus sama dengan konstanta di App\Models\User
        Role::create(['name' => 'Waka / Admin']);      // 1
        Role::create(['name' => 'Guru / Organisasi']); // 2
        Role::create(['name' => 'PIC Ruang']);         // 3

        $admin = User::create([
            'name' => 'Waka Sarana', 'username' => 'admin', 'email' => 'admin@rapi.test', 'password' => '123',
            'phone' => '081200000001', 'role_id' => User::ROLE_ADMIN,
        ]);
        $guru = User::create([
            'name' => 'Rina Kusumawati', 'username' => 'guru', 'email' => 'guru@rapi.test', 'password' => '123',
            'phone' => '081200000002', 'role_id' => User::ROLE_GURU,
        ]);
        $osis = User::create([
            'name' => 'OSIS SMK', 'username' => 'osis', 'email' => 'osis@rapi.test', 'password' => '123',
            'role_id' => User::ROLE_GURU,
        ]);
        $pic1 = User::create([
            'name' => 'Pak Dimas', 'username' => 'pic', 'email' => 'pic@rapi.test', 'password' => '123',
            'phone' => '081234567890', 'role_id' => User::ROLE_PIC,
        ]);
        $pic2 = User::create([
            'name' => 'Bu Sari', 'username' => 'pic2', 'email' => 'pic2@rapi.test', 'password' => '123',
            'phone' => '081298765432', 'role_id' => User::ROLE_PIC,
        ]);

        // Pak Dimas memegang tiga ruang, Bu Sari dua ruang
        $lab1  = Room::create(['name' => 'Lab Komputer 1', 'location' => 'Gedung A lantai 2', 'pic_id' => $pic1->id]);
        $lab2  = Room::create(['name' => 'Lab Komputer 2', 'location' => 'Gedung A lantai 2', 'pic_id' => $pic1->id]);
        $bengkel = Room::create(['name' => 'Bengkel Jaringan', 'location' => 'Gedung B', 'pic_id' => $pic1->id]);
        $aula  = Room::create(['name' => 'Aula', 'location' => 'Gedung Utama', 'pic_id' => $pic2->id]);
        $perpus = Room::create(['name' => 'Perpustakaan', 'location' => 'Gedung Utama lantai 2', 'pic_id' => $pic2->id]);
        Room::create(['name' => 'Ruang Rapat Guru', 'location' => 'Gedung Utama']);

        $besok = now()->addDay()->toDateString();

        Event::create([
            'user_id' => $guru->id, 'room_id' => $lab1->id, 'title' => 'Praktikum Basis Data XI RPL', 'category' => 'Siswa',
            'description' => 'Praktik query SQL dan relasi tabel.', 'status' => Event::DISETUJUI,
            'event_date' => $besok, 'start_time' => '07:45:00', 'end_time' => '10:00:00',
        ]);
        Event::create([
            'user_id' => $osis->id, 'room_id' => $aula->id, 'title' => 'Rapat Persiapan Class Meeting', 'category' => 'Siswa', 'status' => Event::DISETUJUI,
            'event_date' => $besok, 'start_time' => '13:00:00', 'end_time' => '15:15:00',
        ]);
        Event::create([
            'user_id' => $admin->id, 'room_id' => $aula->id, 'title' => 'Sosialisasi Tata Tertib', 'category' => 'Sekolah', 'status' => Event::DISETUJUI,
            'event_date' => now()->addDays(3)->toDateString(), 'start_time' => '07:00:00', 'end_time' => '09:15:00',
        ]);

        // Pengajuan contoh yang menunggu keputusan Waka (untuk mencoba setujui/tolak)
        Event::create([
            'user_id' => $osis->id, 'room_id' => $lab2->id, 'title' => 'Pelatihan Desain Poster OSIS', 'category' => 'Siswa',
            'event_date' => now()->addDays(2)->toDateString(), 'start_time' => '10:15:00', 'end_time' => '11:45:00',
            'status' => Event::MENUNGGU,
        ]);

        Complaint::create([
            'room_id' => $lab2->id, 'reporter_id' => $guru->id, 'pic_id' => $pic1->id,
            'description' => 'AC tidak dingin dan menetes air di dekat meja nomor 5.',
        ]);
        // Laporan lama yang belum ditangani, supaya tombol WhatsApp eskalasi bisa dicoba
        $lama = Complaint::create([
            'room_id' => $bengkel->id, 'reporter_id' => $guru->id, 'pic_id' => $pic1->id,
            'description' => 'Switch rak jaringan mati sebagian, 6 port tidak menyala.',
        ]);
        $lama->forceFill(['created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)])->save();

        Complaint::create([
            'room_id' => $perpus->id, 'reporter_id' => $osis->id, 'pic_id' => $pic2->id,
            'description' => 'Proyektor tidak menampilkan gambar.', 'status_repair' => Complaint::SPARE,
        ]);
    }
}
