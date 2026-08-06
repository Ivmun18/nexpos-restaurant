<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tiendaUno = DB::table('tiendas')->where('codigo', 'T01')->value('id');

        Schema::table('inventario_movimientos', function (Blueprint $table) use ($tiendaUno) {
            $table->unsignedBigInteger('tienda_id')->default($tiendaUno)->after('empresa_id');
        });

        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->foreign('tienda_id')->references('id')->on('tiendas');
            $table->index(['tienda_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->dropIndex(['tienda_id', 'producto_id']);
            $table->dropForeign(['tienda_id']);
            $table->dropColumn('tienda_id');
        });
    }
};
