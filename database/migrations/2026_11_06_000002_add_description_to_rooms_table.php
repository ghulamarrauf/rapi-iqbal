<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('rooms', 'description')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            // Keterangan ruang (kapasitas, jumlah komputer, dsb.) yang tampil di halaman Cari ruangan
            $table->string('description')->nullable()->after('location');
        });

        // Isi keterangan awal untuk ruang yang sudah ada (dicocokkan lewat nama)
        DB::table('rooms')->whereIn('name', ['Ruang 1', 'Ruang 2', 'Ruang 3'])
            ->update(['description' => 'Kapasitas 36 siswa']);
        DB::table('rooms')->where('name', 'like', 'Lab Komputer%')
            ->update(['description' => 'Kapasitas 36 siswa · 36 unit komputer']);
        DB::table('rooms')->where('name', 'Ruang Wijaya Kusuma')
            ->update(['description' => 'Kapasitas 200 orang']);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('rooms', 'description')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
