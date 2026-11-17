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
        Schema::create('tempat_parkir', function (Blueprint $table) {
            $table->id('id_tempat_parkir');
            $table->string('nama_tempat', 100);
            $table->integer('kapasitas_reguler')->default(0);
            $table->integer('kuota_inap')->default(0);
            $table->boolean('sediakan_booking_inap')->default(true);
            $table->boolean('sediakan_member')->default(true);
            $table->string('qris_image', 255)->nullable();
            $table->boolean('status_aktif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tempat_parkir');
    }
};
