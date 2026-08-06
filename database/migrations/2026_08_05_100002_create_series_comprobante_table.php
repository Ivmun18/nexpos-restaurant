<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_comprobante', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('tienda_id');
            $table->string('tipo_comprobante', 2); // 01 factura, 03 boleta, 07 nota credito
            $table->string('serie', 4);
            $table->unsignedInteger('ultimo_correlativo')->default(0);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('cascade');
            $table->unique(['empresa_id', 'tienda_id', 'tipo_comprobante']);
        });

        // Backfill: las series actuales de cada empresa (empresas.serie_boleta,
        // serie_factura, serie_nota_credito) quedaban asignadas de forma global.
        // Se migran tal cual a Tienda 1 (Mundo Llantas), que es donde se emitieron
        // hasta ahora. Tienda 2 (Llantas Pucallpa) NO recibe series aquí: SUNAT
        // exige series propias por establecimiento anexo, y ese rango debe
        // solicitarse/registrarse primero en el portal SUNAT / OSE antes de poder
        // emitir desde Tienda 2. Configurar manualmente cuando se tenga ese dato.
        $tiendaUno = DB::table('tiendas')->where('codigo', 'T01')->value('id');

        $empresas = DB::table('empresas')->get([
            'id', 'serie_boleta', 'ultimo_num_boleta',
            'serie_factura', 'ultimo_num_factura',
            'serie_nota_credito', 'ultimo_num_nota_credito',
        ]);

        $now  = now();
        $rows = [];

        foreach ($empresas as $empresa) {
            if (!empty($empresa->serie_boleta)) {
                $rows[] = [
                    'empresa_id'          => $empresa->id,
                    'tienda_id'           => $tiendaUno,
                    'tipo_comprobante'    => '03',
                    'serie'               => $empresa->serie_boleta,
                    'ultimo_correlativo'  => $empresa->ultimo_num_boleta ?? 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
            if (!empty($empresa->serie_factura)) {
                $rows[] = [
                    'empresa_id'          => $empresa->id,
                    'tienda_id'           => $tiendaUno,
                    'tipo_comprobante'    => '01',
                    'serie'               => $empresa->serie_factura,
                    'ultimo_correlativo'  => $empresa->ultimo_num_factura ?? 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
            if (!empty($empresa->serie_nota_credito)) {
                $rows[] = [
                    'empresa_id'          => $empresa->id,
                    'tienda_id'           => $tiendaUno,
                    'tipo_comprobante'    => '07',
                    'serie'               => $empresa->serie_nota_credito,
                    'ultimo_correlativo'  => $empresa->ultimo_num_nota_credito ?? 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('series_comprobante')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('series_comprobante');
    }
};
