<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estaciones_cocina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nombre'); // Cocina, Freidora, Parrilla, Bar, etc.
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activa', 'inactiva', 'mantenimiento'])->default('activa');
            $table->integer('orden')->default(0); // Para ordenar las estaciones en la UI
            $table->integer('tiempo_estimado_minutos')->default(15); // Tiempo estimado de preparación
            $table->boolean('requiere_validacion')->default(false); // Si necesita validación extra
            $table->timestamps();

            $table->unique(['empresa_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estaciones_cocina');
    }
};
