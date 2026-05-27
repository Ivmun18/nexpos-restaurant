<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->foreignId('miembro_id')->constrained('gimnasio_miembros')->onDelete('cascade');
            $table->timestamp('entrada');
            $table->timestamp('salida')->nullable();
            $table->enum('tipo_acceso', ['normal', 'clase', 'pt'])->default('normal');
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_accesos'); }
};
