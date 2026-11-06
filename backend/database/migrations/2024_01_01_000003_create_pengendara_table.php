<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengendara', function (Blueprint $table) {
            $table->integerIncrements('id_pengendara');
            $table->string('nama_lengkap', 50);
            $table->string('email', 100)->unique();
            $table->string('no_telepon', 15)->nullable();
            $table->string('username', 50)->unique()->nullable();
            $table->string('password', 255)->nullable()->comment('NULL for Google OAuth');
            $table->integer('skor_reputasi')->default(100);
            $table->tinyInteger('status_aktif')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengendara');
    }
};

