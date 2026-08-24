<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_variantes_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('menu_productos')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->boolean('requerido')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variantes_grupo');
    }
};
