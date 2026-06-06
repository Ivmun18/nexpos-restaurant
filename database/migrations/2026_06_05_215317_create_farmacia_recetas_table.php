<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmacia_recetas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('venta_id')->nullable(); // vinculada a una venta
            $table->string('numero_receta')->nullable();        // número de receta del médico
            $table->date('fecha');
            $table->string('medico')->nullable();
            $table->string('especialidad')->nullable();
            $table->string('establecimiento')->nullable();      // hospital/clínica
            $table->string('paciente_nombre');
            $table->string('paciente_dni', 15)->nullable();
            $table->json('items');                              // productos despachados
            $table->enum('estado', ['pendiente', 'despachada', 'parcial'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->index('empresa_id');
            $table->index(['empresa_id', 'fecha']);
            $table->index(['empresa_id', 'paciente_dni']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmacia_recetas');
    }
};
