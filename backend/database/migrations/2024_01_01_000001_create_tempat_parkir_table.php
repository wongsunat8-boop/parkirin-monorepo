<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempat_parkir', function (Blueprint $table) {
            $table->integerIncrements('id_tempat_parkir');
            $table->string('nama_tempat', 100);
            $table->integer('kapasitas')->default(0);
            $table->integer('terisi')->default(0);
            $table->tinyInteger('sediakan_booking')->default(1)->comment('1 = Booking enabled, 0 = Walk-in only');
            $table->tinyInteger('status_aktif')->default(1);

            $table->index(['status_aktif', 'sediakan_booking'], 'idx_tempat_aktif_booking');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tempat_parkir');
    }
};

