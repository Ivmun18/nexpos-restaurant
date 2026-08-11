<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_galeria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('titulo')->nullable();
            $table->string('tratamiento')->nullable();
            $table->string('foto_antes');
            $table->string('foto_despues');
            $table->text('descripcion')->nullable();
            $table->boolean('publica')->default(false);
            $table->timestamps();
            $table->index('empresa_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('odonto_galeria');
    }
};
