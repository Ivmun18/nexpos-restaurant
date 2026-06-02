<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('odonto_pacientes')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('odonto_doctores')->cascadeOnDelete();
            $table->datetime('fecha_hora');
            $table->integer('duracion_min')->default(30);
            $table->enum('estado', ['programada','confirmada','en_curso','completada','cancelada','no_asistio'])->default('programada');
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_citas'); }
};
