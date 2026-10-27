<?php

use App\Database\SchemaSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        SchemaSync::table('tb_position', [
            'id'         => fn (Blueprint $table) => $table->integer('id', true),
            'name'       => fn (Blueprint $table) => $table->string('name', 100)->nullable(),
            'created_by' => fn (Blueprint $table) => $table->integer('created_by')->nullable()->index(),
            'created_at' => fn (Blueprint $table) => $table->dateTime('created_at')->nullable(),
            'updated_by' => fn (Blueprint $table) => $table->integer('updated_by')->nullable()->index(),
            'updated_at' => fn (Blueprint $table) => $table->dateTime('updated_at')->nullable(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_position');
    }
};
