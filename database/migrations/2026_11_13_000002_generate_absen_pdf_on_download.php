<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_absen', function (Blueprint $table)
        {
            $table->integer('disetujui_oleh')->nullable()->after('keterangan_admin');
            $table->dropColumn('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('tb_absen', function (Blueprint $table)
        {
            $table->string('pdf_path')->nullable();
            $table->dropColumn('disetujui_oleh');
        });
    }
};
