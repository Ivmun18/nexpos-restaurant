<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_sesiones_pt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('miembro_id')->constrained('gimnasio_miembros')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('gimnasio_instructores')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->integer('duracion_min')->default(60);
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('incluida_en_plan')->default(false);
            $table->enum('estado', ['programada', 'realizada', 'cancelada'])->default('programada');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_sesiones_pt'); }
};
