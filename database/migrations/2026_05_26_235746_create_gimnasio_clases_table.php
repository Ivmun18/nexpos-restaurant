<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->string('nombre'); // Yoga, Spinning, Zumba, etc.
            $table->string('tipo')->nullable(); // cardio, fuerza, flexibilidad
            $table->integer('capacidad_max')->default(20);
            $table->integer('duracion_min')->default(60);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_clases'); }
};
