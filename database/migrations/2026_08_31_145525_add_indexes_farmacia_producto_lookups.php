<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // venta_detalle.producto_id no tenía índice: usado en kardex()
        // (Farmacia\ProductosFarmaciaController) para traer todas las
        // salidas de un producto vía join con ventas, y en el cálculo de
        // top_productos de reportes — full scan de venta_detalle en ambos
        // casos a medida que crece la tabla.
        Schema::table('venta_detalle', function (Blueprint $table) {
            $table->index('producto_id');
        });

        // productos.codigo_barras no tenía índice pese a ser el campo de
        // búsqueda principal del escaneo (POS y InventarioInicialController::buscar).
        Schema::table('productos', function (Blueprint $table) {
            $table->index('codigo_barras');
        });
    }

    public function down(): void
    {
        Schema::table('venta_detalle', function (Blueprint $table) {
            $table->dropIndex(['producto_id']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['codigo_barras']);
        });
    }
};
