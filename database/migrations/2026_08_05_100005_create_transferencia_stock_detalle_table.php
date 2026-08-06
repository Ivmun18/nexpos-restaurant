<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencia_stock_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transferencia_id');
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 12, 3);
            $table->timestamps();

            $table->foreign('transferencia_id')->references('id')->on('transferencias_stock')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencia_stock_detalle');
    }
};
