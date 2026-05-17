<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comandas_cocina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('pedido_detalle_id')->constrained('pedido_detalles')->onDelete('cascade');
            $table->foreignId('estacion_cocina_id')->constrained('estaciones_cocina')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // El mozo que hace el pedido

            // Estado de la comanda
            $table->enum('estado', [
                'pendiente',        // Acaba de llegar a cocina
                'aceptada',         // Chef aceptó
                'en_preparacion',   // Se está preparando
                'lista',            // Lista para servir
                'entregada',        // Entregada al mozo
                'rechazada'         // El chef rechazó (sin stock, etc)
            ])->default('pendiente');

            // Información de producto
            $table->string('nombre_producto', 150);
            $table->integer('cantidad')->default(1);
            $table->text('notas')->nullable(); // Notas especiales: "Sin picante", "Extra queso", etc.

            // Tiempos
            $table->timestamp('hora_creacion')->useCurrent();
            $table->timestamp('hora_aceptacion')->nullable();
            $table->timestamp('hora_inicio_preparacion')->nullable();
            $table->timestamp('hora_terminacion')->nullable();
            $table->timestamp('hora_entrega_mozo')->nullable();
            $table->integer('tiempo_preparacion_minutos')->nullable(); // Tiempo real de preparación

            // Priorización
            $table->integer('numero_ronda')->default(1); // Ronda del pedido
            $table->boolean('es_urgente')->default(false);
            $table->integer('prioridad')->default(0); // Mayor número = mayor prioridad

            // Auditoría
            $table->foreignId('chef_asignado_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('motivo_rechazo')->nullable();
            $table->timestamps();

            // Índices para optimizar búsquedas
            $table->index(['empresa_id', 'estado']);
            $table->index(['estacion_cocina_id', 'estado']);
            $table->index(['hora_creacion', 'estado']);
            $table->index('pedido_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comandas_cocina');
    }
};
