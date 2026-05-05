<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo_comprobante', 2)->default('01');
            $table->string('serie_proveedor', 4)->nullable();
            $table->unsignedInteger('correlativo_proveedor')->nullable();
            $table->string('numero_comprobante', 13)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('tipo_cambio', 10, 4)->default(1);
            $table->decimal('total_gravado', 12, 2)->default(0);
            $table->decimal('total_exonerado', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('forma_pago', ['contado','credito'])->default('contado');
            $table->enum('estado', ['pendiente','recibido','contabilizado','anulado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id')->references('id')->on('proveedores');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};