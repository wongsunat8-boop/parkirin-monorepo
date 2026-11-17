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
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id('id_kendaraan');
            $table->string('plat_nomor', 15)->unique();
            $table->enum('jenis_kendaraan', ['Motor', 'Mobil', 'Sepeda'])->default('Motor');
            $table->string('warna', 20)->nullable();
            $table->foreignId('id_pengendara')->nullable()->constrained('pengendara', 'id_pengendara')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
