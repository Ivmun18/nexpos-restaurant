<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una compra sigue siendo de una sola empresa (la factura del proveedor
     * llega a un RUC específico), pero ahora indica también en qué tienda
     * entra la mercadería a producto_stocks.
     */
    public function up(): void
    {
        $tiendaUno = DB::table('tiendas')->where('codigo', 'T01')->value('id');

        Schema::table('compras', function (Blueprint $table) use ($tiendaUno) {
            $table->unsignedBigInteger('tienda_id')->default($tiendaUno)->after('empresa_id');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->foreign('tienda_id')->references('id')->on('tiendas');
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['tienda_id']);
            $table->dropColumn('tienda_id');
        });
    }
};
