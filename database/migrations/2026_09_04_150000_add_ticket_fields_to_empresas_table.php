<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('serie_ticket', 10)->default('T001')->after('serie_nota_credito_factura');
            $table->integer('ultimo_num_ticket')->default(0)->after('serie_ticket');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['serie_ticket', 'ultimo_num_ticket']);
        });
    }
};
