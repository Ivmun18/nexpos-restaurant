<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_presentaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->string('nombre');
            $table->string('unidad_sunat', 10)->default('NIU');
            $table->decimal('factor_conversion', 12, 4)->default(1);
            $table->decimal('precio_venta', 10, 2);
            $table->string('codigo_barras')->nullable();
            $table->boolean('es_default')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_presentaciones');
    }
};
