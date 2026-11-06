<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            // Diisi otomatis saat Waka/Admin memindahkan ruang (overriding)
            $table->text('move_reason')->nullable();
            $table->foreignId('moved_from_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->timestamp('moved_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
