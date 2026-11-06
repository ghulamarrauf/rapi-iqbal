<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Menunggu | Disetujui | Ditolak
            $table->string('status', 20)->default('Menunggu')->after('end_time');
            $table->text('reject_reason')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('reject_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->index('event_date');
        });

        // Agenda yang sudah ada sebelum fitur persetujuan dianggap sudah disetujui
        DB::table('events')->update(['status' => 'Disetujui']);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['event_date']);
            $table->dropColumn(['status', 'reject_reason', 'reviewed_by', 'reviewed_at']);
        });
    }
};
