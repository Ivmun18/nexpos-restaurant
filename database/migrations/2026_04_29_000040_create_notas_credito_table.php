<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_credito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('venta_id');
            // Identificación
            $table->string('tipo_comprobante', 2)->default('07');
            $table->string('serie', 4);
            $table->unsignedInteger('correlativo');
            $table->string('numero_completo', 13)->nullable();
            $table->date('fecha_emision');
            // Documento de referencia
            $table->string('doc_ref_tipo', 2);
            $table->string('doc_ref_serie', 4);
            $table->unsignedInteger('doc_ref_correlativo');
            $table->string('doc_ref_numero', 13);
            // Motivo
            $table->string('motivo_codigo', 2);
            $table->string('motivo_descripcion', 200);
            // Cliente
            $table->string('cliente_tipo_doc', 1)->nullable();
            $table->string('cliente_num_doc', 15)->nullable();
            $table->string('cliente_razon_social', 200)->nullable();
            // Moneda
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('tipo_cambio', 10, 4)->default(1.0000);
            // Totales
            $table->decimal('total_gravado', 12, 2)->default(0);
            $table->decimal('total_exonerado', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            // Estado
            $table->enum('estado', ['borrador','emitido','aceptado','rechazado','anulado'])->default('emitido');
            $table->datetime('fecha_envio_sunat')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'serie', 'correlativo']);
            $table->foreign('venta_id')->references('id')->on('ventas');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_credito');
    }
};
