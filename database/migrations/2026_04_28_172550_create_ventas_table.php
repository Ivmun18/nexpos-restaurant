<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            // Tipo de comprobante
            $table->string('tipo_comprobante', 2)->default('03');
            $table->string('serie', 4)->default('B001');
            $table->unsignedInteger('correlativo')->default(1);
            $table->string('numero_completo', 13)->nullable();
            $table->date('fecha_emision');
            $table->time('hora_emision');
            // Datos del cliente en el momento de la venta
            $table->string('cliente_tipo_doc', 1)->default('1');
            $table->string('cliente_num_doc', 15)->nullable();
            $table->string('cliente_razon_social', 200)->nullable();
            $table->string('cliente_direccion', 300)->nullable();
            $table->string('cliente_email', 150)->nullable();
            // Moneda
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('tipo_cambio', 10, 4)->default(1.0000);
            // Totales
            $table->decimal('total_gravado', 12, 2)->default(0);
            $table->decimal('total_exonerado', 12, 2)->default(0);
            $table->decimal('total_inafecto', 12, 2)->default(0);
            $table->decimal('total_descuento', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total_isc', 12, 2)->default(0);
            $table->decimal('total_icbper', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            // Pago
            $table->enum('forma_pago', ['contado','credito'])->default('contado');
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('vuelto', 12, 2)->default(0);
            // Estado
            $table->enum('estado', ['borrador','emitido','aceptado','rechazado','anulado'])->default('emitido');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'fecha_emision']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};