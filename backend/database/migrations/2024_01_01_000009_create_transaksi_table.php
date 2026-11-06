<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->integerIncrements('id_transaksi');
            $table->unsignedInteger('id_booking')->nullable()->unique()->comment('NULL if walk-in');
            $table->unsignedInteger('id_kendaraan');
            $table->unsignedInteger('id_tempat_parkir');
            $table->unsignedInteger('id_tarif');
            $table->unsignedInteger('id_pengendara')->nullable()->comment('NULL if offline customer');
            $table->unsignedInteger('id_petugas')->nullable()->comment('ID User (Petugas) who served');
            $table->dateTime('waktu_masuk')->useCurrent();
            $table->dateTime('waktu_keluar')->nullable();
            $table->integer('durasi_jam')->nullable();
            $table->decimal('biaya_total', 15, 2)->nullable();
            $table->enum('status_pembayaran', ['Belum Bayar', 'Sudah Bayar'])->default('Belum Bayar');
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');

            $table->index(['id_tempat_parkir', 'status', 'id_transaksi'], 'idx_transaksi_tenant_keyset');
            $table->index(['id_pengendara', 'id_transaksi'], 'idx_transaksi_pengendara_keyset');
            $table->index(['id_tempat_parkir', 'waktu_masuk', 'status_pembayaran'], 'idx_transaksi_waktu_rekap');

            $table->foreign('id_booking', 'fk_transaksi_booking')
                  ->references('id_booking')->on('booking')
                  ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('id_kendaraan', 'fk_transaksi_kendaraan')
                  ->references('id_kendaraan')->on('kendaraan')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('id_tempat_parkir', 'fk_transaksi_tempat_parkir')
                  ->references('id_tempat_parkir')->on('tempat_parkir')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_tarif', 'fk_transaksi_tarif')
                  ->references('id_tarif')->on('tarif')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('id_pengendara', 'fk_transaksi_pengendara')
                  ->references('id_pengendara')->on('pengendara')
                  ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('id_petugas', 'fk_transaksi_petugas')
                  ->references('id_user')->on('user')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};

