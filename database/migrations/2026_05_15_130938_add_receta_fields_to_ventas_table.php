<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('receta_medico_nombre', 150)->nullable()->after('caja_id');
            $table->string('receta_medico_cmp', 20)->nullable()->after('receta_medico_nombre');
            $table->string('receta_numero', 50)->nullable()->after('receta_medico_cmp');
            $table->date('receta_fecha')->nullable()->after('receta_numero');
            $table->text('receta_observaciones')->nullable()->after('receta_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['receta_medico_nombre', 'receta_medico_cmp', 'receta_numero', 'receta_fecha', 'receta_observaciones']);
        });
    }
};
