<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cuentas_por_pagar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->string('numero_documento')->nullable();
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('monto_pendiente', 12, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_emision');
            $table->enum('estado', ['pendiente', 'parcial', 'pagado', 'vencido', 'anulado'])->default('pendiente');
            $table->string('forma_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('cascade');
            $table->foreign('compra_id')->references('id')->on('compras')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_pagar');
    }
};