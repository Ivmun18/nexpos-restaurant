<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_instructores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('dni', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('especialidad')->nullable(); // Yoga, Crossfit, Spinning
            $table->decimal('comision_clase', 10, 2)->default(0); // por clase grupal
            $table->decimal('comision_pt', 10, 2)->default(0);    // por sesión PT
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_instructores'); }
};
