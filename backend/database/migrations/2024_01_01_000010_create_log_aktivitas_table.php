<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->integerIncrements('id_log');
            $table->unsignedInteger('id_tempat_parkir')->nullable()->comment('NULL for Super Admin actions');
            $table->unsignedInteger('id_user');
            $table->string('aktivitas', 150);
            $table->dateTime('waktu_aktivitas')->useCurrent();

            $table->index(['id_tempat_parkir', 'id_log'], 'idx_log_tenant_keyset');

            $table->foreign('id_tempat_parkir', 'fk_log_tempat_parkir')
                  ->references('id_tempat_parkir')->on('tempat_parkir')
                  ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('id_user', 'fk_log_user')
                  ->references('id_user')->on('user')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};

