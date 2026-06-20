<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_recetas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('ficha_id')->nullable();
            $table->string('numero_receta')->nullable();
            $table->date('fecha');
            $table->string('tipo')->default('lejos')->comment('lejos, cerca, bifocal, progresivo');
            $table->text('indicaciones')->nullable();
            $table->timestamps();
            $table->index(['empresa_id', 'paciente_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_recetas');
    }
};
