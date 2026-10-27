<?php

use App\Database\SchemaSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        SchemaSync::table('tb_sto', [
            'id'         => fn (Blueprint $table) => $table->bigIncrements('id'),
            'kode_sto'   => fn (Blueprint $table) => $table->string('kode_sto', 20)->unique(),
            'nama_sto'   => fn (Blueprint $table) => $table->string('nama_sto', 100),
            'alamat'     => fn (Blueprint $table) => $table->string('alamat'),
            'kecamatan'  => fn (Blueprint $table) => $table->string('kecamatan', 100)->nullable(),
            'latitude'   => fn (Blueprint $table) => $table->decimal('latitude', 10, 7),
            'longitude'  => fn (Blueprint $table) => $table->decimal('longitude', 10, 7),
            'status'     => fn (Blueprint $table) => $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif'),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_sto');
    }
};
