<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una caja física es de la tienda (recibe pagos que cubren comprobantes
     * de cualquiera de las empresas), ya no de una sola empresa.
     * empresa_id se deja en la tabla sin uso (deprecado) hasta que los
     * controladores se actualicen; no se elimina en esta migración.
     */
    public function up(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->unsignedBigInteger('tienda_id')->nullable()->after('empresa_id');
        });

        $tiendaUno = DB::table('tiendas')->where('codigo', 'T01')->value('id');
        DB::table('caja')->update(['tienda_id' => $tiendaUno]);

        Schema::table('caja', function (Blueprint $table) {
            $table->unsignedBigInteger('tienda_id')->nullable(false)->change();
            $table->foreign('tienda_id')->references('id')->on('tiendas');

            $table->dropUnique('caja_empresa_id_codigo_unique');
            $table->unique(['tienda_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropUnique(['tienda_id', 'codigo']);
            $table->unique(['empresa_id', 'codigo']);
            $table->dropForeign(['tienda_id']);
            $table->dropColumn('tienda_id');
        });
    }
};
