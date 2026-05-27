<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_planes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->string('nombre'); // Mensual, Trimestral, Anual, etc.
            $table->decimal('precio', 10, 2);
            $table->integer('duracion_dias'); // 30, 90, 365
            $table->boolean('incluye_clases')->default(false);
            $table->boolean('incluye_pt')->default(false); // entrenador personal
            $table->integer('sesiones_pt')->default(0); // cuántas sesiones PT incluye
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_planes'); }
};
