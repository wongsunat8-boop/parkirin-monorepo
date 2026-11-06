<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->integerIncrements('id_kendaraan');
            $table->string('plat_nomor', 15)->unique();
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil', 'Sepeda']);
            $table->string('warna', 20);
            $table->unsignedInteger('id_pengendara')->nullable();
            $table->unsignedInteger('id_user_input')->nullable()->comment('ID Admin/Petugas if registered offline');

            $table->index('id_pengendara', 'idx_kendaraan_pengendara');

            $table->foreign('id_pengendara', 'fk_kendaraan_pengendara')
                  ->references('id_pengendara')->on('pengendara')
                  ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('id_user_input', 'fk_kendaraan_user_input')
                  ->references('id_user')->on('user')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};

