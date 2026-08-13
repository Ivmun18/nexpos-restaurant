<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documenta un cambio de esquema que ya estaba aplicado en producción sin
 * migración: tipo_acto pasó de ENUM (8 valores fijos) a VARCHAR(100)
 * nullable para soportar la taxonomía ampliada de ~40 tipos de acto usada
 * por el wizard de expedientes (compra_venta, poder, testamento, etc.),
 * que ya no cabía en el enum original.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actos_notariales', function (Blueprint $table) {
            $table->string('tipo_acto', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('actos_notariales', function (Blueprint $table) {
            $table->enum('tipo_acto', [
                'escritura_publica',
                'poder',
                'testamento',
                'legalizacion',
                'carta_notarial',
                'protesto',
                'acta_notarial',
                'otro',
            ])->nullable(false)->change();
        });
    }
};
