<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comprobantes_sunat', function (Blueprint $table) {
            $table->string('forma_pago', 20)->default('Contado')->after('estado');
        });

        Schema::create('cuotas_credito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comprobante_id');
            $table->unsignedBigInteger('empresa_id');
            $table->tinyInteger('numero_cuota');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_vencimiento');
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->date('fecha_pago')->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente, pagada
            $table->string('metodo_pago', 30)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->timestamps();
            $table->foreign('comprobante_id')->references('id')->on('comprobantes_sunat')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas_credito');
        Schema::table('comprobantes_sunat', function (Blueprint $table) {
            $table->dropColumn('forma_pago');
        });
    }
};
