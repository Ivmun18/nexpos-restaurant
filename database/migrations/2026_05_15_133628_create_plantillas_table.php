<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('industry_type', 30);             // farmacia, ferreteria, minimarket
            $table->string('codigo', 50)->unique();          // farmacia_basica, farmacia_amplia
            $table->string('nombre', 100);                   // "Farmacia Básica (100 medicamentos)"
            $table->text('descripcion')->nullable();
            $table->integer('total_productos')->default(0);
            $table->integer('total_categorias')->default(0);
            $table->string('seeder_class', 100)->nullable(); // FarmaciaBasicaSeeder
            $table->boolean('activa')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
            $table->index(['industry_type', 'activa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas');
    }
};
