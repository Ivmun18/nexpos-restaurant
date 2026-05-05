<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sunat_respuestas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('numero_completo', 13);
            $table->string('tipo_comprobante', 2);
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tabla', 50)->nullable();
            $table->string('codigo_respuesta', 10)->nullable();
            $table->string('descripcion_respuesta', 500)->nullable();
            $table->longText('notas_cdr')->nullable();
            $table->longText('xml_cdr')->nullable();
            $table->string('ticket_sunat', 50)->nullable();
            $table->datetime('fecha_envio');
            $table->datetime('fecha_respuesta')->nullable();
            $table->tinyInteger('intento')->default(1);
            $table->boolean('exitoso')->default(false);
            $table->text('error_tecnico')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'numero_completo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sunat_respuestas');
    }
};