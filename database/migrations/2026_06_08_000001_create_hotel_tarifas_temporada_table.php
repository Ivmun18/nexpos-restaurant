<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotel_tarifas_temporada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('tipo_id')->nullable()->constrained('hotel_tipos_habitacion')->onDelete('cascade'); // null = aplica a todos los tipos
            $table->string('nombre'); // "Temporada Alta", "Fiestas Patrias", etc.
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('precio_noche', 10, 2);
            $table->string('color')->default('#3B82F6'); // para visual en calendario
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_tarifas_temporada');
    }
};
