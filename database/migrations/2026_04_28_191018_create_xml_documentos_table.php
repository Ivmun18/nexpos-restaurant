<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xml_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('tipo_comprobante', 2);
            $table->string('numero_completo', 13);
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tabla', 50)->nullable();
            $table->longText('xml_sin_firma')->nullable();
            $table->longText('xml_firmado')->nullable();
            $table->string('hash_documento', 100)->nullable();
            $table->string('nombre_archivo', 100);
            $table->string('zip_nombre', 100)->nullable();
            $table->timestamps();

           $table->unique(['empresa_id', 'tipo_comprobante', 'numero_completo'], 'xml_docs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xml_documentos');
    }
};
