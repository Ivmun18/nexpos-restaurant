<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes_trabajo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->string('numero', 20);
            $table->string('cliente_nombre', 200);
            $table->string('cliente_telefono', 20)->nullable();
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('trabajos_realizados')->nullable();
            $table->string('estado', 30)->default('pendiente'); // pendiente, en_proceso, completada, entregada, cancelada
            $table->string('prioridad', 20)->default('normal'); // baja, normal, alta, urgente
            $table->date('fecha_ingreso');
            $table->date('fecha_estimada')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->decimal('costo_mano_obra', 10, 2)->default(0);
            $table->decimal('costo_materiales', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ordenes_trabajo');
    }
};
