<?php

use App\Database\SchemaSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        SchemaSync::table('tb_alat_kerja', [
            'id'                 => fn (Blueprint $table) => $table->bigIncrements('id'),
            'nik_karyawan'       => fn (Blueprint $table) => $table->integer('nik_karyawan')->index(),
            'kode_alat'          => fn (Blueprint $table) => $table->string('kode_alat', 50)->unique(),
            'nama_alat'          => fn (Blueprint $table) => $table->string('nama_alat', 100),
            'tanggal_penyerahan' => fn (Blueprint $table) => $table->date('tanggal_penyerahan'),
            'kondisi'            => fn (Blueprint $table) => $table->enum('kondisi', ['Baik', 'Rusak'])->default('Baik'),
            'status'             => fn (Blueprint $table) => $table->enum('status', ['Sudah Dikembalikan', 'Belum Dikembalikan'])->default('Belum Dikembalikan'),
            'created_by'         => fn (Blueprint $table) => $table->unsignedBigInteger('created_by')->nullable(),
            'created_at'         => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_by'         => fn (Blueprint $table) => $table->unsignedBigInteger('updated_by')->nullable(),
            'updated_at'         => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_alat_kerja');
    }
};
