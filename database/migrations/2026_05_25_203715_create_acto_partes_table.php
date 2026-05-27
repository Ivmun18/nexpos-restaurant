<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acto_partes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acto_id')->constrained('actos_notariales')->cascadeOnDelete();
            
            // Datos personales
            $table->string('tipo_persona')->default('natural'); // natural, juridica
            $table->string('tipo_documento', 10); // 1=DNI, 6=RUC, 4=CE, 7=Pasaporte
            $table->string('numero_documento', 20);
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('razon_social')->nullable(); // Para personas jurídicas
            
            // Rol en el acto
            $table->string('rol'); // vendedor, comprador, otorgante, beneficiario, testigo, etc.
            $table->integer('orden')->default(1); // Orden de aparición
            
            // Estado civil y régimen
            $table->string('estado_civil')->nullable(); // soltero, casado, viudo, divorciado
            $table->string('regimen_patrimonial')->nullable(); // sociedad_gananciales, separacion_bienes
            $table->string('nombre_conyuge')->nullable();
            $table->string('dni_conyuge')->nullable();
            
            // Domicilio
            $table->text('domicilio')->nullable();
            $table->string('distrito')->nullable();
            $table->string('provincia')->nullable();
            $table->string('departamento')->nullable();
            
            // Contacto
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            
            // Representación legal (si aplica)
            $table->boolean('actua_mediante_representante')->default(false);
            $table->string('nombre_representante')->nullable();
            $table->string('dni_representante')->nullable();
            $table->string('tipo_poder')->nullable(); // general, especial, judicial
            $table->text('facultades_representante')->nullable();
            
            // Datos adicionales
            $table->string('profesion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['acto_id', 'rol']);
            $table->index('numero_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acto_partes');
    }
};
