<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'category')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            // Jenis agenda untuk penanda warna di kalender: Siswa | Guru
            $table->string('category', 20)->default('Guru')->after('title');
        });

        // Agenda lama yang diajukan akun organisasi siswa (OSIS) dianggap event siswa.
        // Dapat diubah per agenda lewat form peminjaman.
        DB::statement("UPDATE events SET category = 'Siswa' WHERE user_id IN (SELECT id FROM users WHERE LOWER(name) LIKE '%osis%')");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('events', 'category')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
