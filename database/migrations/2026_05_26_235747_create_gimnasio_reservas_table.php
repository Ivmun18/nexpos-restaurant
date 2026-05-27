<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('miembro_id')->constrained('gimnasio_miembros')->onDelete('cascade');
            $table->foreignId('horario_id')->constrained('gimnasio_horarios')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('estado', ['reservado', 'asistio', 'no_asistio', 'cancelado'])->default('reservado');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_reservas'); }
};
