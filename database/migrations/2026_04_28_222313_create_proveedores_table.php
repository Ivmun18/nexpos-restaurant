<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->char('tipo_documento', 1)->default('6');
            $table->string('numero_documento', 15);
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('direccion', 300)->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->char('pais_codigo', 2)->default('PE');
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('contacto_nombre', 100)->nullable();
            $table->tinyInteger('dias_credito')->default(0);
            $table->boolean('agente_retencion')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'tipo_documento', 'numero_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};