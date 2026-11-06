<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'email')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Email tertaut ke akun; dipakai untuk mengirim OTP lupa password.
            // Nullable hanya agar akun lama tetap valid: mereka dipaksa mengisinya saat login berikutnya.
            $table->string('email')->nullable()->unique()->after('username');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'email')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
};
