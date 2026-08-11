<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas_hotel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('monto_inicial_original', 10, 2)->nullable();
            $table->decimal('total_efectivo', 10, 2)->default(0);
            $table->decimal('total_yape', 10, 2)->default(0);
            $table->decimal('total_plin', 10, 2)->default(0);
            $table->decimal('total_tarjeta', 10, 2)->default(0);
            $table->decimal('total_ventas', 10, 2)->default(0);
            $table->integer('cantidad_ventas')->default(0);
            $table->decimal('monto_final', 10, 2)->nullable();
            $table->decimal('monto_final_original', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_correccion')->nullable();
            $table->unsignedBigInteger('corregido_por_id')->nullable();
            $table->timestamp('corregido_at')->nullable();
            $table->string('estado')->default('abierta');
            $table->timestamp('apertura_at')->nullable();
            $table->timestamp('cierre_at')->nullable();
            $table->timestamps();

            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas_hotel');
    }
};
