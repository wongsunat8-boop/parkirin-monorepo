<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id('id_log');
            $table->foreignId('id_tempat_parkir')->nullable()->constrained('tempat_parkir', 'id_tempat_parkir')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('user', 'id_user')->cascadeOnDelete();
            $table->string('aktivitas', 150);
            $table->dateTime('waktu_aktivitas')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
