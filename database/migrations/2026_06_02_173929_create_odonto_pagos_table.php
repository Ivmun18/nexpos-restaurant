<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('odonto_pacientes')->cascadeOnDelete();
            $table->foreignId('presupuesto_id')->nullable()->constrained('odonto_presupuestos')->nullOnDelete();
            $table->date('fecha');
            $table->decimal('monto_total', 10, 2);
            $table->integer('num_cuotas')->default(1);
            $table->enum('tipo_pago', ['contado','cuotas'])->default('contado');
            $table->enum('estado', ['pendiente','parcial','pagado','anulado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_pagos'); }
};
