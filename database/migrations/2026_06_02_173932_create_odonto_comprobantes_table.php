<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pago_id')->nullable()->constrained('odonto_pagos')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('odonto_pacientes')->cascadeOnDelete();
            $table->enum('tipo_comprobante', ['01','03'])->default('03');
            $table->string('serie');
            $table->integer('numero');
            $table->date('fecha_emision');
            $table->decimal('total', 10, 2);
            $table->string('cliente_nombre');
            $table->string('cliente_documento')->nullable();
            $table->enum('estado', ['pendiente','aceptado','rechazado','anulado'])->default('pendiente');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_comprobantes'); }
};
