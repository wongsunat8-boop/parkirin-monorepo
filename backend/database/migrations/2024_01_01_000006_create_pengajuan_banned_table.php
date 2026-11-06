<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_banned', function (Blueprint $table) {
            $table->integerIncrements('id_banned');
            $table->unsignedInteger('id_tempat_parkir');
            $table->unsignedInteger('id_pengaju')->comment('ID User (Admin) who submitted the ban');
            $table->unsignedInteger('id_pengendara')->nullable();
            $table->unsignedInteger('id_kendaraan')->nullable();
            $table->string('alasan', 100);
            $table->enum('status_pengajuan', ['Pending', 'Disetujui', 'Ditolak', 'Dicabut'])->default('Pending');
            $table->dateTime('tanggal_pengajuan')->useCurrent();

            $table->index(
                ['id_tempat_parkir', 'status_pengajuan', 'id_kendaraan', 'id_pengendara'],
                'idx_banned_lookup'
            );

            $table->foreign('id_tempat_parkir', 'fk_banned_tempat_parkir')
                  ->references('id_tempat_parkir')->on('tempat_parkir')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_pengaju', 'fk_banned_pengaju')
                  ->references('id_user')->on('user')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_pengendara', 'fk_banned_pengendara')
                  ->references('id_pengendara')->on('pengendara')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_kendaraan', 'fk_banned_kendaraan')
                  ->references('id_kendaraan')->on('kendaraan')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_banned');
    }
};

