<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sucursal_id nullable: NULL = mesa no asignada a una sucursal
     * especifica (empresas con una sola sucursal siguen funcionando igual).
     */
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('empresa_id')->constrained('sucursales');
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
