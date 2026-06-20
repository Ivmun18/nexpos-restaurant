<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('receta_id')->nullable();
            $table->unsignedBigInteger('caja_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('numero_venta');
            $table->date('fecha');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('vuelto', 10, 2)->default(0);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'yape', 'plin', 'transferencia'])->default('efectivo');
            $table->enum('tipo_comprobante', ['boleta', 'factura', 'ticket'])->default('boleta');
            $table->string('ruc_cliente')->nullable();
            $table->string('razon_social_cliente')->nullable();
            $table->string('serie_comprobante')->nullable();
            $table->string('numero_comprobante')->nullable();
            $table->enum('estado', ['pendiente', 'pagado', 'anulado'])->default('pagado');
            $table->string('sunat_estado')->nullable();
            $table->text('sunat_respuesta')->nullable();
            $table->timestamps();
            $table->index('empresa_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_ventas');
    }
};
