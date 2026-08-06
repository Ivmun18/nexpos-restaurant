<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tiendaUno = DB::table('tiendas')->where('codigo', 'T01')->value('id');

        Schema::table('ventas', function (Blueprint $table) use ($tiendaUno) {
            // Default = Tienda 1: mientras los controladores del POS no se
            // actualicen para setear tienda_id explícitamente, cualquier
            // venta nueva sigue cayendo en la tienda donde opera hoy el negocio.
            $table->unsignedBigInteger('tienda_id')->default($tiendaUno)->after('empresa_id');
            // Nullable: se llena históricamente abajo y, a futuro, lo setea
            // el checkout del POS al crear el grupo antes que sus comprobantes.
            $table->unsignedBigInteger('venta_grupo_id')->nullable()->after('tienda_id');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('tienda_id')->references('id')->on('tiendas');
            $table->foreign('venta_grupo_id')->references('id')->on('venta_grupos');
        });

        // Backfill histórico: 1 venta_grupo por cada venta existente (relación
        // 1:1 para datos pasados, ya que el POS anterior no agrupaba
        // comprobantes de varias empresas en un mismo checkout).
        DB::table('ventas')
            ->select('id', 'usuario_id', 'cliente_id', 'cliente_tipo_doc', 'cliente_num_doc',
                     'cliente_razon_social', 'cliente_direccion', 'cliente_email',
                     'forma_pago', 'metodo_pago', 'monto_pagado', 'vuelto', 'total', 'estado')
            ->orderBy('id')
            ->chunkById(200, function ($ventas) use ($tiendaUno) {
                foreach ($ventas as $venta) {
                    $grupoId = DB::table('venta_grupos')->insertGetId([
                        'tienda_id'            => $tiendaUno,
                        'sesion_caja_id'       => null,
                        'usuario_id'           => $venta->usuario_id,
                        'cliente_id'           => $venta->cliente_id,
                        'cliente_tipo_doc'     => $venta->cliente_tipo_doc,
                        'cliente_num_doc'      => $venta->cliente_num_doc,
                        'cliente_razon_social' => $venta->cliente_razon_social,
                        'cliente_direccion'    => $venta->cliente_direccion,
                        'cliente_email'        => $venta->cliente_email,
                        'forma_pago'           => $venta->forma_pago,
                        'metodo_pago'          => $venta->metodo_pago,
                        'total_general'        => $venta->total,
                        'monto_pagado'         => $venta->monto_pagado,
                        'vuelto'               => $venta->vuelto,
                        'estado'               => $venta->estado === 'anulado' ? 'anulado' : 'emitido',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);

                    DB::table('ventas')->where('id', $venta->id)->update([
                        'tienda_id'      => $tiendaUno,
                        'venta_grupo_id' => $grupoId,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['tienda_id']);
            $table->dropForeign(['venta_grupo_id']);
            $table->dropColumn(['tienda_id', 'venta_grupo_id']);
        });
    }
};
