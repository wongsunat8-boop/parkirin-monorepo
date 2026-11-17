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
        Schema::create('user', function (Blueprint $table) {
            $table->id('id_user');
            $table->foreignId('id_tempat_parkir')->nullable()->constrained('tempat_parkir', 'id_tempat_parkir')->nullOnDelete()->cascadeOnUpdate();
            $table->string('nama_lengkap', 50);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('role', ['super_admin', 'owner', 'admin', 'petugas']);
            $table->boolean('status_aktif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
