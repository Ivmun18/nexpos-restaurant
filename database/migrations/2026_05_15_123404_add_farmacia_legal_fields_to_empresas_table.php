<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('qf_regente_nombre', 150)->nullable()->after('industry_type');
            $table->string('qf_regente_cqfp', 20)->nullable()->after('qf_regente_nombre');
            $table->string('numero_digemid', 30)->nullable()->after('qf_regente_cqfp');
            $table->string('autorizacion_sanitaria', 100)->nullable()->after('numero_digemid');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['qf_regente_nombre', 'qf_regente_cqfp', 'numero_digemid', 'autorizacion_sanitaria']);
        });
    }
};
