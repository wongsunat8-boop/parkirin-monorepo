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
        Schema::create('langganan_member', function (Blueprint $table) {
            $table->id('id_langganan');
            $table->string('kode_member', 30)->unique();
            $table->foreignId('id_tempat_parkir')->constrained('tempat_parkir', 'id_tempat_parkir')->cascadeOnDelete();
            $table->foreignId('id_paket')->constrained('paket_langganan', 'id_paket')->restrictOnDelete();
            $table->foreignId('id_pengendara')->constrained('pengendara', 'id_pengendara')->cascadeOnDelete();
            $table->foreignId('id_kendaraan')->constrained('kendaraan', 'id_kendaraan')->restrictOnDelete();
            $table->foreignId('id_user_verifikasi')->nullable()->constrained('user', 'id_user')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->decimal('biaya_langganan', 15, 2);
            $table->string('bukti_bayar', 255);
            $table->dateTime('waktu_unggah_bukti');
            $table->enum('status_pembayaran', ['Pending', 'Lunas', 'Ditolak'])->default('Pending');
            $table->enum('status_langganan', ['Aktif', 'Kedaluwarsa', 'Dibatalkan'])->default('Aktif');
            $table->dateTime('waktu_registrasi')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('langganan_member');
    }
};
