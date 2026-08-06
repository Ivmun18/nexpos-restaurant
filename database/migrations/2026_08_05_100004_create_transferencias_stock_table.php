<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tienda_origen_id');
            $table->unsignedBigInteger('tienda_destino_id');
            $table->unsignedBigInteger('usuario_id');
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'enviada', 'recibida', 'anulada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('tienda_origen_id')->references('id')->on('tiendas');
            $table->foreign('tienda_destino_id')->references('id')->on('tiendas');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias_stock');
    }
};
