<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * numero era unico por empresa; ahora es unico por empresa+sucursal,
     * para permitir que dos sucursales de la misma empresa tengan cada una
     * su propia "Mesa 1". Nota: sucursal_id es nullable y MySQL no trata
     * dos NULL como iguales en un indice unico, asi que mesas sin sucursal
     * asignada pierden la proteccion de numero duplicado dentro de esa
     * empresa (antes si la tenian).
     */
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropUnique('mesas_empresa_id_numero_unique');
            $table->unique(['empresa_id', 'sucursal_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'sucursal_id', 'numero']);
            $table->unique(['empresa_id', 'numero']);
        });
    }
};
