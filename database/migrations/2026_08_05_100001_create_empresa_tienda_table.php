<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_tienda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('tienda_id');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('cascade');
            $table->unique(['empresa_id', 'tienda_id']);
        });

        // Hoy ambas empresas venden en ambas tiendas: se enlazan todas las
        // combinaciones existentes en lugar de asumir IDs fijos.
        $empresas = DB::table('empresas')->pluck('id');
        $tiendas  = DB::table('tiendas')->pluck('id');
        $now      = now();

        $rows = [];
        foreach ($empresas as $empresaId) {
            foreach ($tiendas as $tiendaId) {
                $rows[] = [
                    'empresa_id' => $empresaId,
                    'tienda_id'  => $tiendaId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('empresa_tienda')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_tienda');
    }
};
