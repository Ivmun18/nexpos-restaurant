<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('numero', 20);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('cliente_tipo_doc', 1)->nullable();
            $table->string('cliente_num_doc', 15)->nullable();
            $table->string('cliente_razon_social', 200)->nullable();
            $table->string('cliente_direccion', 300)->nullable();
            $table->string('cliente_email', 150)->nullable();
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('total_gravado', 12, 2)->default(0);
            $table->decimal('total_exonerado', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['borrador','enviada','aprobada','rechazada','vencida','convertida'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->text('terminos_condiciones')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};