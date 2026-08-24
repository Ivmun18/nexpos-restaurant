<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sucursal_id = sucursal donde trabaja el mozo/cajero/cocinero. NULL =
     * acceso a todas las sucursales de su empresa (admin). Sin backfill
     * automatico: se asigna manualmente via SucursalSeeder o el panel admin.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('empresa_id')->constrained('sucursales');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
