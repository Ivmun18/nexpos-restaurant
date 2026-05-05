<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes_sunat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('caja_restaurante_id')->nullable()->constrained('caja_restaurante')->onDelete('set null');
            
            // Datos del comprobante
            $table->string('tipo_comprobante', 2); // 01=Factura, 03=Boleta, 07=NC
            $table->string('serie', 4);
            $table->integer('numero');
            $table->date('fecha_emision');
            
            // Cliente
            $table->string('cliente_tipo_documento', 1); // 1=DNI, 6=RUC
            $table->string('cliente_numero_documento', 11);
            $table->string('cliente_nombre');
            $table->string('cliente_direccion')->nullable();
            $table->string('cliente_email')->nullable();
            
            // Montos
            $table->decimal('total_gravada', 10, 2);
            $table->decimal('total_igv', 10, 2);
            $table->decimal('total', 10, 2);
            
            // Respuesta SUNAT
            $table->boolean('aceptada_por_sunat')->default(false);
            $table->text('sunat_descripcion')->nullable();
            $table->string('codigo_hash')->nullable();
            
            // Enlaces de archivos
            $table->text('enlace_pdf')->nullable();
            $table->text('enlace_xml')->nullable();
            $table->text('enlace_cdr')->nullable();
            
            // Estado
            $table->enum('estado', ['emitido', 'aceptado', 'rechazado', 'anulado'])->default('emitido');
            
            $table->timestamps();
            
            // Índices con nombres cortos
            $table->index(['empresa_id', 'serie', 'numero'], 'idx_comprobante');
            $table->index('fecha_emision', 'idx_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_sunat');
    }
};
