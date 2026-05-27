<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('miembro_id')->constrained('gimnasio_miembros')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('gimnasio_planes')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'yape', 'plin', 'transferencia', 'tarjeta']);
            $table->string('referencia')->nullable(); // nro operación yape/plin
            $table->date('fecha_pago');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->string('comprobante_tipo')->nullable(); // boleta, factura
            $table->string('comprobante_serie')->nullable();
            $table->integer('comprobante_numero')->nullable();
            $table->enum('estado', ['pagado', 'anulado'])->default('pagado');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_pagos'); }
};
