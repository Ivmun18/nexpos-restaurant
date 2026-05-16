<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_nombre', 100)->nullable(); // snapshot por si borran el user
            
            // Categoría del evento
            $table->enum('categoria', [
                'venta',
                'compra',
                'producto',
                'inventario',
                'caja',
                'usuario',
                'auth',
                'sistema'
            ]);
            
            // Acción específica
            $table->string('accion', 50); // creada, editada, eliminada, vendido_vencido, login, etc.
            
            // Recurso afectado
            $table->string('entidad', 50)->nullable();          // 'producto', 'venta', 'usuario'
            $table->unsignedBigInteger('entidad_id')->nullable(); // ID del recurso
            $table->string('entidad_descripcion', 200)->nullable(); // "Paracetamol 500mg"
            
            // Detalles del cambio (JSON)
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            
            // Contexto
            $table->text('descripcion')->nullable();  // texto humano de lo que pasó
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            
            // Severidad (para alertas)
            $table->enum('severidad', ['info', 'warning', 'critical'])->default('info');
            
            $table->timestamps();
            $table->index(['empresa_id', 'created_at']);
            $table->index(['empresa_id', 'categoria']);
            $table->index(['empresa_id', 'usuario_id']);
            $table->index(['empresa_id', 'severidad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_log');
    }
};
