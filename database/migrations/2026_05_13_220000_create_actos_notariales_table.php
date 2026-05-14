<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actos_notariales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(10);
            $table->string('numero_expediente', 50)->unique();
            $table->enum('tipo_acto', [
                'escritura_publica',
                'poder',
                'testamento',
                'legalizacion',
                'carta_notarial',
                'protesto',
                'acta_notarial',
                'otro'
            ]);
            $table->string('asunto', 300);
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->enum('estado', ['pendiente', 'en_proceso', 'finalizado', 'cancelado'])->default('pendiente');
            $table->date('fecha_ingreso');
            $table->date('fecha_entrega')->nullable();
            $table->decimal('monto_cobrar', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->enum('estado_pago', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->text('partes_intervinientes')->nullable();
            $table->timestamps();
        });

        Schema::create('acto_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acto_id')->constrained('actos_notariales')->onDelete('cascade');
            $table->string('nombre', 200);
            $table->string('archivo', 500)->nullable();
            $table->string('tipo', 50)->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();
        });

        Schema::create('acto_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acto_id')->constrained('actos_notariales')->onDelete('cascade');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('estado_nuevo', ['pendiente', 'en_proceso', 'finalizado', 'cancelado']);
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acto_seguimientos');
        Schema::dropIfExists('acto_documentos');
        Schema::dropIfExists('actos_notariales');
    }
};
