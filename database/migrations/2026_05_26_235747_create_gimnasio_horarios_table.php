<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('clase_id')->constrained('gimnasio_clases')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('gimnasio_instructores')->onDelete('cascade');
            $table->enum('dia', ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_horarios'); }
};
