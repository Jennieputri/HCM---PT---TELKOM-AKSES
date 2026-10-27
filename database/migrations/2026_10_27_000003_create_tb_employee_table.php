<?php

use App\Database\SchemaSync;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        SchemaSync::table('tb_employee', [
            'id'             => fn (Blueprint $table) => $table->integer('id', true),
            'role_id'        => fn (Blueprint $table) => $table->integer('role_id')->nullable()->index(),
            'nik'            => fn (Blueprint $table) => $table->integer('nik')->nullable()->index(),
            'name'           => fn (Blueprint $table) => $table->string('name', 100)->nullable(),
            'number_phone'   => fn (Blueprint $table) => $table->string('number_phone', 15)->nullable(),
            'email'          => fn (Blueprint $table) => $table->string('email', 50)->nullable(),
            'address'        => fn (Blueprint $table) => $table->text('address')->nullable(),
            'remember_token' => fn (Blueprint $table) => $table->string('remember_token')->nullable(),
            'password'       => fn (Blueprint $table) => $table->string('password')->nullable(),
            'status'         => fn (Blueprint $table) => $table->integer('status')->nullable(),
            'created_by'     => fn (Blueprint $table) => $table->integer('created_by')->nullable()->index(),
            'created_at'     => fn (Blueprint $table) => $table->dateTime('created_at')->nullable(),
            'updated_by'     => fn (Blueprint $table) => $table->integer('updated_by')->nullable()->index(),
            'updated_at'     => fn (Blueprint $table) => $table->dateTime('updated_at')->nullable(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_employee');
    }
};
