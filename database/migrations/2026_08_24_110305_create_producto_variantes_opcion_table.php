<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_variantes_opcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('producto_variantes_grupo')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variantes_opcion');
    }
};
