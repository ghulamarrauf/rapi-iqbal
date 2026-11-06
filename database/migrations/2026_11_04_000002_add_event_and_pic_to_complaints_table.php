<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Peminjaman yang sedang berlangsung saat kerusakan dilaporkan (opsional)
            $table->foreignId('event_id')->nullable()->after('reporter_id')->constrained('events')->nullOnDelete();
            // PIC ditentukan otomatis dari ruang saat laporan dibuat
            $table->foreignId('pic_id')->nullable()->after('event_id')->constrained('users')->nullOnDelete();
        });

        // Laporan lama: PIC diisi dari PIC ruang saat ini
        DB::statement('UPDATE complaints SET pic_id = (SELECT pic_id FROM rooms WHERE rooms.id = complaints.room_id) WHERE pic_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['pic_id']);
            $table->dropColumn(['event_id', 'pic_id']);
        });
    }
};
