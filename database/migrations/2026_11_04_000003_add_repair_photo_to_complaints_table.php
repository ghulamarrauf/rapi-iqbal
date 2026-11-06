<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Foto bukti perbaikan yang diunggah PIC saat status menjadi "Sudah Diperbaiki"
            $table->string('repair_photo_path')->nullable()->after('photo_path');
            $table->timestamp('repaired_at')->nullable()->after('status_repair');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['repair_photo_path', 'repaired_at']);
        });
    }
};
