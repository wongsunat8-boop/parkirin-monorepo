<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->integerIncrements('id_user');
            $table->unsignedInteger('id_tempat_parkir')->nullable()->comment('NULL for super_admin');
            $table->string('nama_lengkap', 50);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('role', ['super_admin', 'owner', 'admin', 'petugas']);
            $table->tinyInteger('status_aktif')->default(1);

            $table->index(['id_tempat_parkir', 'role', 'status_aktif'], 'idx_user_tenant_role');

            $table->foreign('id_tempat_parkir', 'fk_user_tempat_parkir')
                  ->references('id_tempat_parkir')->on('tempat_parkir')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};

