<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('odonto_pacientes')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('odonto_doctores')->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('estado', ['borrador','enviado','aprobado','rechazado','completado'])->default('borrador');
            $table->decimal('total', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_presupuestos'); }
};
