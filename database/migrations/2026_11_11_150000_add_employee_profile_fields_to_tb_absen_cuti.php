<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_absen_cuti', function (Blueprint $table) {
            $table->string('band', 100)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('unit_kerja', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tb_absen_cuti', function (Blueprint $table) {
            $table->dropColumn(['band', 'jabatan', 'unit_kerja']);
        });
    }
};
