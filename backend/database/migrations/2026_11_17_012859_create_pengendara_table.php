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
        Schema::create('pengendara', function (Blueprint $table) {
            $table->id('id_pengendara');
            $table->string('nama_lengkap', 50);
            $table->string('email', 100)->unique();
            $table->string('no_telepon', 15)->nullable();
            $table->string('username', 50)->unique()->nullable();
            $table->string('password', 255)->nullable();
            $table->boolean('status_aktif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengendara');
    }
};
