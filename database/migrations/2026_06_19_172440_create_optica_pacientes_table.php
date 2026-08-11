<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_pacientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('dni', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M','F'])->nullable();
            $table->text('direccion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index('empresa_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_pacientes');
    }
};
