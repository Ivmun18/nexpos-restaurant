<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_id');
            $table->unsignedBigInteger('usuario_id');
            $table->datetime('fecha_apertura')->default(now());
            $table->decimal('monto_apertura', 12, 2)->default(0);
            $table->datetime('fecha_cierre')->nullable();
            $table->decimal('monto_cierre_sistema', 12, 2)->nullable();
            $table->decimal('monto_cierre_real', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();
            $table->enum('estado', ['abierta','cerrada'])->default('abierta');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('caja_id')->references('id')->on('caja');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_caja');
    }
};