<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('emitir_boleta_por_defecto')->default(false)->after('zona_exonerada');
        });

        // Punto de Encuentro: nunca ticket interno, siempre boleta electronica
        // aunque el cajero no ingrese datos del cliente.
        DB::table('empresas')->where('id', 23)->update(['emitir_boleta_por_defecto' => true]);
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('emitir_boleta_por_defecto');
        });
    }
};
