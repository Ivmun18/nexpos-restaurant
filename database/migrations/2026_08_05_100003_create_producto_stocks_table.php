<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('tienda_id');
            $table->decimal('stock_actual', 12, 3)->default(0);
            $table->decimal('stock_minimo', 12, 3)->default(0);
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('cascade');
            $table->unique(['producto_id', 'tienda_id']);
        });

        // Backfill: todo el inventario existente hoy está físicamente en
        // Tienda 1 (Mundo Llantas). Tienda 2 (Llantas Pucallpa) arranca en cero
        // y se carga a futuro por compra directa o transferencia desde Tienda 1.
        $tiendaUno = DB::table('tiendas')->where('codigo', 'T01')->value('id');
        $now       = now();

        DB::table('productos')
            ->select('id', 'stock_actual', 'stock_minimo')
            ->orderBy('id')
            ->chunkById(500, function ($productos) use ($tiendaUno, $now) {
                $rows = $productos->map(fn ($p) => [
                    'producto_id' => $p->id,
                    'tienda_id'   => $tiendaUno,
                    'stock_actual' => $p->stock_actual ?? 0,
                    'stock_minimo' => $p->stock_minimo ?? 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ])->toArray();

                DB::table('producto_stocks')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_stocks');
    }
};
