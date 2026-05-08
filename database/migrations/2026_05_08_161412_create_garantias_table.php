<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->string('numero', 20);
            $table->string('cliente_nombre', 200);
            $table->string('cliente_telefono', 20)->nullable();
            $table->string('producto_descripcion', 255);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('serie', 100)->nullable();
            $table->date('fecha_compra');
            $table->date('fecha_vencimiento');
            $table->integer('meses_garantia')->default(12);
            $table->string('estado', 30)->default('activa'); // activa, vencida, reclamada, anulada
            $table->text('condiciones')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('garantias');
    }
};
