<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gimnasio_miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('dni', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['M', 'F', 'otro'])->nullable();
            $table->string('foto')->nullable();
            $table->string('codigo_qr')->nullable()->unique(); // para control acceso
            $table->foreignId('plan_id')->nullable()->constrained('gimnasio_planes')->nullOnDelete();
            $table->date('membrecia_inicio')->nullable();
            $table->date('membrecia_vencimiento')->nullable();
            $table->enum('estado', ['activo', 'vencido', 'suspendido', 'inactivo'])->default('inactivo');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gimnasio_miembros'); }
};
