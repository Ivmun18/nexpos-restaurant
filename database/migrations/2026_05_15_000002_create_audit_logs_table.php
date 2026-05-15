<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('accion'); // create, update, delete, view
            $table->string('modulo'); // Compras, Ventas, Productos, etc.
            $table->string('modelo')->nullable(); // Nombre del modelo
            $table->unsignedBigInteger('registro_id')->nullable(); // ID del registro modificado
            $table->longText('cambios_anteriores')->nullable(); // JSON
            $table->longText('cambios_nuevos')->nullable(); // JSON
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('detalles')->nullable();
            $table->timestamps();
            
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['empresa_id', 'created_at']);
            $table->index(['modulo', 'accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};