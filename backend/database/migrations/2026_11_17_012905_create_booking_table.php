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
        Schema::create('booking', function (Blueprint $table) {
            $table->id('id_booking');
            $table->string('kode_booking', 30)->unique();
            $table->foreignId('id_tempat_parkir')->constrained('tempat_parkir', 'id_tempat_parkir')->cascadeOnDelete();
            $table->foreignId('id_pengendara')->constrained('pengendara', 'id_pengendara')->cascadeOnDelete();
            $table->foreignId('id_kendaraan')->constrained('kendaraan', 'id_kendaraan')->restrictOnDelete();
            $table->foreignId('id_user_verifikasi')->nullable()->constrained('user', 'id_user')->nullOnDelete();
            $table->dateTime('waktu_booking')->useCurrent();
            $table->dateTime('waktu_mulai_inap');
            $table->dateTime('waktu_rencana_keluar');
            $table->decimal('biaya_inap_dibayar', 15, 2)->default(0);
            $table->string('bukti_bayar', 255)->nullable();
            $table->dateTime('waktu_unggah_bukti')->nullable();
            $table->enum('status_pembayaran', ['Pending', 'Lunas', 'Ditolak'])->default('Pending');
            $table->enum('status', ['Terjadwal', 'Aktif_Inap', 'Overstay', 'Selesai', 'Batal'])->default('Terjadwal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
