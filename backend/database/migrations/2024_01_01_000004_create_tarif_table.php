<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif', function (Blueprint $table) {
            $table->integerIncrements('id_tarif');
            $table->unsignedInteger('id_tempat_parkir');
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil', 'Sepeda']);
            $table->enum('tipe_tarif', ['Per Jam', 'Flat Harian', 'Progresif Mal'])->default('Per Jam');
            $table->decimal('tarif_per_jam', 15, 2);
            $table->decimal('tarif_jam_pertama', 15, 2)->default(0.00);
            $table->decimal('tarif_berikutnya', 15, 2)->default(0.00);
            $table->decimal('tarif_inap', 15, 2)->default(0.00);
            $table->tinyInteger('status_aktif')->default(1);

            $table->unique(['id_tempat_parkir', 'jenis_kendaraan'], 'uq_tarif_tenant_kendaraan');

            $table->foreign('id_tempat_parkir', 'fk_tarif_tempat_parkir')
                  ->references('id_tempat_parkir')->on('tempat_parkir')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif');
    }
};

