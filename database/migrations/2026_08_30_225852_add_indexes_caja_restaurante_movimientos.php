<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // caja_restaurante.empresa_id no tenía índice pese a ser el filtro
        // más usado (todos los reportes y turnos filtran por empresa_id +
        // rango de fechas) — full scan en cada reporte.
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->index(['empresa_id', 'created_at']);
        });

        // caja_movimientos.referencia_id no tenía índice pese a ser la
        // columna de join hacia caja_restaurante usada en el desglose de
        // caja por sesión (ver CajaController@index).
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->index('referencia_id');
        });
    }

    public function down(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'created_at']);
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropIndex(['referencia_id']);
        });
    }
};
