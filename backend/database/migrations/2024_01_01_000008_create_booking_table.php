<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->integerIncrements('id_booking');
            $table->unsignedInteger('id_pengendara');
            $table->unsignedInteger('id_tempat_parkir');
            $table->unsignedInteger('id_kendaraan');
            $table->dateTime('waktu_booking')->useCurrent();
            $table->dateTime('waktu_rencana_kedatangan');
            $table->string('kode_booking', 50)->unique();
            $table->enum('status', ['Menunggu', 'Selesai', 'Batal'])->default('Menunggu');

            $table->index(
                ['id_tempat_parkir', 'status', 'waktu_rencana_kedatangan'],
                'idx_booking_tenant_status'
            );
            $table->index(['id_pengendara', 'id_booking'], 'idx_booking_pengendara_keyset');

            $table->foreign('id_pengendara', 'fk_booking_pengendara')
                  ->references('id_pengendara')->on('pengendara')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_tempat_parkir', 'fk_booking_tempat_parkir')
                  ->references('id_tempat_parkir')->on('tempat_parkir')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_kendaraan', 'fk_booking_kendaraan')
                  ->references('id_kendaraan')->on('kendaraan')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};

