<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_historia_clinica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('odonto_pacientes')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('odonto_doctores')->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('odonto_citas')->nullOnDelete();
            $table->date('fecha');
            $table->text('anamnesis')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('tratamiento_realizado')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_historia_clinica'); }
};
