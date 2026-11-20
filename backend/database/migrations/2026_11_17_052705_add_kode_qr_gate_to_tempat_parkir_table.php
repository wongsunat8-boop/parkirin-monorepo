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
        Schema::table('tempat_parkir', function (Blueprint $table) {
            $table->string('kode_qr_gate', 100)->nullable()->unique()->after('nama_tempat');
            $table->timestamp('qr_generated_at')->nullable()->after('kode_qr_gate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tempat_parkir', function (Blueprint $table) {
            $table->dropColumn(['kode_qr_gate', 'qr_generated_at']);
        });
    }
};
