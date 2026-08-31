<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * tienda_id = tienda física donde trabaja el cajero. NULL = acceso a
     * todas las tiendas (dueño/admin). No se hace backfill automático:
     * no hay forma segura de inferir aquí quién es cajero de qué tienda vs.
     * quién es admin. Asignar manualmente después de correr esta migración.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tienda_id')->nullable()->after('empresa_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tienda_id')->references('id')->on('tiendas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tienda_id']);
            $table->dropColumn('tienda_id');
        });
    }
};
