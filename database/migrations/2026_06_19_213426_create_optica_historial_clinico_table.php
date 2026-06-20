<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_historial_clinico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('ficha_id')->nullable();
            $table->date('fecha');
            $table->text('motivo_consulta')->nullable();
            $table->text('antecedentes')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('proxima_cita')->nullable();
            $table->timestamps();
            $table->index(['empresa_id','paciente_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_historial_clinico');
    }
};
