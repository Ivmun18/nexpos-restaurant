<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_pago_cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('odonto_pagos')->cascadeOnDelete();
            $table->integer('numero_cuota');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->enum('metodo_pago', ['efectivo','yape','plin','tarjeta','transferencia'])->nullable();
            $table->enum('estado', ['pendiente','pagado','vencido'])->default('pendiente');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_pago_cuotas'); }
};
