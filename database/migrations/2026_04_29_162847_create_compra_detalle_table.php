<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('descripcion', 500);
            $table->string('unidad_medida', 10)->default('NIU');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio_unitario', 12, 4);
            $table->decimal('valor_unitario', 12, 4);
            $table->string('tipo_afectacion_igv', 2)->default('10');
            $table->decimal('total_valor', 12, 2);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->foreign('compra_id')->references('id')->on('compras')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_detalle');
    }
};