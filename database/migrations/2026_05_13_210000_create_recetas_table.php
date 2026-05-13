<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_producto_id')->constrained('menu_productos')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->decimal('cantidad', 10, 4);
            $table->timestamps();
            $table->unique(['menu_producto_id', 'insumo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
