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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->string('kode_karcis', 30)->unique();
            $table->foreignId('id_tempat_parkir')->constrained('tempat_parkir', 'id_tempat_parkir')->cascadeOnDelete();
            $table->foreignId('id_kendaraan')->constrained('kendaraan', 'id_kendaraan')->restrictOnDelete();
            $table->foreignId('id_tarif')->constrained('tarif', 'id_tarif')->restrictOnDelete();
            $table->foreignId('id_booking')->unique()->nullable()->constrained('booking', 'id_booking')->nullOnDelete();
            $table->foreignId('id_langganan')->nullable()->constrained('langganan_member', 'id_langganan')->nullOnDelete();
            $table->foreignId('id_petugas_masuk')->nullable()->constrained('user', 'id_user')->nullOnDelete();
            $table->foreignId('id_petugas_keluar')->nullable()->constrained('user', 'id_user')->nullOnDelete();
            $table->dateTime('waktu_masuk')->useCurrent();
            $table->dateTime('waktu_keluar')->nullable();
            $table->decimal('biaya_dasar', 15, 2)->default(0);
            $table->decimal('denda_overstay', 15, 2)->default(0);
            $table->decimal('denda_karcis_hilang', 15, 2)->default(0);
            $table->string('identitas_pengambil', 60)->nullable();
            $table->enum('metode_pembayaran', ['Tunai', 'QRIS_Mitra', 'Prepaid_Booking', 'Member_Pass'])->default('Tunai');
            $table->enum('status_pembayaran', ['Belum Bayar', 'Sudah Bayar'])->default('Belum Bayar');
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
