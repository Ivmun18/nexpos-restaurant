<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('minuta_notario_nombre')->nullable()->after('minuta_ciudad');
            $table->string('minuta_notario_dni')->nullable()->after('minuta_notario_nombre');
            $table->string('minuta_resolucion_ministerial')->nullable()->after('minuta_notario_dni');
            $table->string('minuta_fecha_resolucion')->nullable()->after('minuta_resolucion_ministerial');
            $table->string('minuta_registro_notario')->nullable()->after('minuta_fecha_resolucion');
            $table->string('minuta_colegio_notarios')->nullable()->after('minuta_registro_notario');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['minuta_notario_nombre','minuta_notario_dni','minuta_resolucion_ministerial','minuta_fecha_resolucion','minuta_registro_notario','minuta_colegio_notarios']);
        });
    }
};
