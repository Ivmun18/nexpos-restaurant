<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "venta_grupos" es el evento de checkout en caja: un carrito, un pago,
     * un cliente. Cuando el carrito mezcla productos de distintas empresas,
     * se emite 1 fila en "ventas" (comprobante) por empresa involucrada,
     * todas apuntando al mismo venta_grupo_id.
     */
    public function up(): void
    {
        Schema::create('venta_grupos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tienda_id');
            $table->unsignedBigInteger('sesion_caja_id')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cliente_id')->nullable();

            // Snapshot del cliente al momento de la venta (igual que hoy en
            // "ventas"). Es la fuente de verdad para los comprobantes emitidos,
            // independiente de si el cliente está registrado en el catálogo de
            // una empresa u otra.
            $table->string('cliente_tipo_doc', 1)->default('1');
            $table->string('cliente_num_doc', 15)->nullable();
            $table->string('cliente_razon_social', 200)->nullable();
            $table->string('cliente_direccion', 300)->nullable();
            $table->string('cliente_email', 150)->nullable();

            $table->enum('forma_pago', ['contado', 'credito'])->default('contado');
            $table->string('metodo_pago', 30)->nullable();
            $table->decimal('total_general', 12, 2)->default(0);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('vuelto', 12, 2)->default(0);

            $table->enum('estado', ['emitido', 'anulado'])->default('emitido');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('tienda_id')->references('id')->on('tiendas');
            $table->foreign('sesion_caja_id')->references('id')->on('sesiones_caja');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->index(['tienda_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_grupos');
    }
};
