<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_receta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('odonto_recetas')->cascadeOnDelete();
            $table->string('medicamento');
            $table->string('dosis')->nullable();
            $table->string('frecuencia')->nullable();
            $table->string('duracion')->nullable();
            $table->text('indicaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_receta_items'); }
};
