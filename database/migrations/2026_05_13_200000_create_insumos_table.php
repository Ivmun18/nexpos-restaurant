<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('nombre', 150);
            $table->string('categoria', 80)->nullable();
            $table->string('unidad_medida', 20)->default('kg');
            $table->decimal('stock_actual', 10, 3)->default(0);
            $table->decimal('stock_minimo', 10, 3)->default(0);
            $table->decimal('precio_promedio', 10, 4)->default(0);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('insumo_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 3);
            $table->decimal('costo_unitario', 10, 4)->default(0);
            $table->decimal('stock_anterior', 10, 3)->default(0);
            $table->decimal('stock_nuevo', 10, 3)->default(0);
            $table->string('motivo', 200)->nullable();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumo_movimientos');
        Schema::dropIfExists('insumos');
    }
};
