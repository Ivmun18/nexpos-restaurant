<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('odonto_tratamientos_catalogo');
        Schema::create('odonto_tratamientos_catalogo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->string('categoria')->default('general');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('duracion_minutos')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('empresa_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('odonto_tratamientos_catalogo');
    }
};
