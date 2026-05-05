<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Columnas de Nubefact
            $table->string('nubefact_token')->nullable()->after('email');
            $table->boolean('nubefact_demo')->default(true)->after('nubefact_token');
            
            // Series de comprobantes
            $table->string('serie_boleta', 4)->default('B001')->after('nubefact_demo');
            $table->string('serie_factura', 4)->default('F001')->after('serie_boleta');
            $table->string('serie_nota_credito', 4)->default('BC01')->after('serie_factura');
            
            // Contadores
            $table->integer('ultimo_num_boleta')->default(0)->after('serie_nota_credito');
            $table->integer('ultimo_num_factura')->default(0)->after('ultimo_num_boleta');
            $table->integer('ultimo_num_nota_credito')->default(0)->after('ultimo_num_factura');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'nubefact_token',
                'nubefact_demo',
                'serie_boleta',
                'serie_factura',
                'serie_nota_credito',
                'ultimo_num_boleta',
                'ultimo_num_factura',
                'ultimo_num_nota_credito',
            ]);
        });
    }
};
