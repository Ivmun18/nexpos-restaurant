<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Campos para APISunat (sin especificar after para evitar errores)
            $table->string('apisunat_token')->nullable();
            $table->string('apisunat_ruc')->nullable();
            $table->string('apisunat_usuario_sol')->nullable();
            $table->string('apisunat_clave_sol')->nullable();
            $table->boolean('apisunat_demo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'apisunat_token',
                'apisunat_ruc',
                'apisunat_usuario_sol',
                'apisunat_clave_sol',
                'apisunat_demo',
            ]);
        });
    }
};
