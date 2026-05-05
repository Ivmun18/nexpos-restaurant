<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_categoria_id')->constrained('menu_categorias')->onDelete('cascade');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('imagen', 500)->nullable();
            $table->boolean('disponible')->default(true);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->integer('tiempo_preparacion')->default(10); // minutos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_productos');
    }
};
