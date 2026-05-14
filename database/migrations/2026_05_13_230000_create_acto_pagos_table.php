<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acto_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acto_id')->constrained('actos_notariales')->onDelete('cascade');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'yape', 'plin', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->enum('tipo', ['adelanto', 'pago_final', 'pago_parcial'])->default('pago_parcial');
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acto_pagos');
    }
};
