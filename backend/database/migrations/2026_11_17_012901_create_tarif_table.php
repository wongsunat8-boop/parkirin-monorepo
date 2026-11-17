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
        Schema::create('tarif', function (Blueprint $table) {
            $table->id('id_tarif');
            $table->foreignId('id_tempat_parkir')->constrained('tempat_parkir', 'id_tempat_parkir')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil', 'Sepeda']);
            $table->enum('tipe_tarif', ['Per Jam', 'Flat Harian', 'Progresif Mal'])->default('Flat Harian');
            $table->decimal('tarif_per_jam', 15, 2)->default(0);
            $table->decimal('tarif_jam_pertama', 15, 2)->default(0);
            $table->decimal('tarif_berikutnya', 15, 2)->default(0);
            $table->decimal('tarif_inap_harian', 15, 2)->default(0);
            $table->decimal('denda_karcis_hilang', 15, 2)->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->unique(['id_tempat_parkir', 'jenis_kendaraan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif');
    }
};
