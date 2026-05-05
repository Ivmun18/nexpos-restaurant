<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sesion_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo', ['ingreso','egreso']);
            $table->string('concepto', 200);
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->decimal('monto', 12, 2);
            $table->string('observaciones', 300)->nullable();
            $table->timestamps();

            $table->foreign('sesion_id')->references('id')->on('sesiones_caja');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};