<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_horarios_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('odonto_doctores')->cascadeOnDelete();
            $table->enum('dia_semana', ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_horarios_doctor'); }
};
