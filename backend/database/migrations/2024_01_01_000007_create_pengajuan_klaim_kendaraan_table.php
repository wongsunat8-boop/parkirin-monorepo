<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_klaim_kendaraan', function (Blueprint $table) {
            $table->integerIncrements('id_klaim');
            $table->unsignedInteger('id_kendaraan');
            $table->unsignedInteger('id_pengaju')->comment('Pengendara who submits the claim');
            $table->unsignedInteger('id_pengendara_target')->comment('Current owner');
            $table->enum('status_klaim', ['Pending', 'Disetujui', 'Ditolak', 'Dibatalkan'])->default('Pending');
            $table->dateTime('tanggal_pengajuan')->useCurrent();

            $table->index(['id_pengendara_target', 'status_klaim'], 'idx_klaim_target_status');

            $table->foreign('id_kendaraan', 'fk_klaim_kendaraan')
                  ->references('id_kendaraan')->on('kendaraan')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_pengaju', 'fk_klaim_pengaju')
                  ->references('id_pengendara')->on('pengendara')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_pengendara_target', 'fk_klaim_target')
                  ->references('id_pengendara')->on('pengendara')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_klaim_kendaraan');
    }
};

