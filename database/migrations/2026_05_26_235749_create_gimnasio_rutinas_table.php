<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_rutinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('miembro_id')->constrained('gimnasio_miembros')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('gimnasio_instructores')->nullOnDelete();
            $table->string('nombre'); // Rutina semana 1, Rutina fuerza, etc.
            $table->json('ejercicios')->nullable(); // [{nombre, series, reps, peso, descanso}]
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_rutinas'); }
};
