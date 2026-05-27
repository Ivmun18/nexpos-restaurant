<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indice_notarial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('acto_id')->constrained('actos_notariales')->cascadeOnDelete();
            
            // Numeración del índice
            $table->string('numero_indice', 20)->unique();
            $table->integer('numero_correlativo');
            $table->integer('anio');
            
            // Datos del acto
            $table->string('tipo_acto', 50);
            $table->text('asunto');
            $table->text('partes');
            $table->decimal('monto', 12, 2)->default(0);
            
            // Fechas
            $table->dateTime('fecha_otorgamiento');
            $table->dateTime('fecha_registro');
            
            // Control
            $table->foreignId('usuario_registro_id')->constrained('users');
            $table->boolean('cerrado')->default(false);
            $table->string('hash', 64)->nullable();
            
            $table->timestamps();
            
            $table->index(['empresa_id', 'anio', 'numero_correlativo']);
            $table->index('fecha_otorgamiento');
            $table->index('tipo_acto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indice_notarial');
    }
};
