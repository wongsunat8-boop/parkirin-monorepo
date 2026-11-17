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
        Schema::create('paket_langganan', function (Blueprint $table) {
            $table->id('id_paket');
            $table->foreignId('id_tempat_parkir')->constrained('tempat_parkir', 'id_tempat_parkir')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_paket', 50);
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil', 'Sepeda']);
            $table->integer('durasi_hari')->default(30);
            $table->integer('batas_masuk_harian')->nullable();
            $table->decimal('harga_paket', 15, 2);
            $table->boolean('status_aktif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_langganan');
    }
};
