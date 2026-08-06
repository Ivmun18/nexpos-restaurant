<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiendas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
            $table->string('direccion', 300)->nullable();
            $table->char('ubigeo', 6)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tiendas físicas reales del negocio.
        // Tienda 1 concentra hoy todo el inventario existente (ver producto_stocks).
        DB::table('tiendas')->insert([
            [
                'codigo'     => 'T01',
                'nombre'     => 'Mundo Llantas',
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo'     => 'T02',
                'nombre'     => 'Llantas Pucallpa',
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tiendas');
    }
};
