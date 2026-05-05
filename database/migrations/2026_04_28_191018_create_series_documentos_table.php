<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('tipo_comprobante', 2);
            $table->string('serie', 4);
            $table->unsignedInteger('correlativo_actual')->default(0);
            $table->string('punto_emision', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'tipo_comprobante', 'serie']);
        });

        // Insertar series iniciales
        DB::table('series_documentos')->insert([
            ['empresa_id'=>1, 'tipo_comprobante'=>'01', 'serie'=>'F001', 'correlativo_actual'=>0, 'activo'=>1, 'created_at'=>now(), 'updated_at'=>now()],
            ['empresa_id'=>1, 'tipo_comprobante'=>'03', 'serie'=>'B001', 'correlativo_actual'=>0, 'activo'=>1, 'created_at'=>now(), 'updated_at'=>now()],
            ['empresa_id'=>1, 'tipo_comprobante'=>'07', 'serie'=>'FC01', 'correlativo_actual'=>0, 'activo'=>1, 'created_at'=>now(), 'updated_at'=>now()],
            ['empresa_id'=>1, 'tipo_comprobante'=>'07', 'serie'=>'BC01', 'correlativo_actual'=>0, 'activo'=>1, 'created_at'=>now(), 'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('series_documentos');
    }
};
