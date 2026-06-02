<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_pedidos_laboratorio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->constrained('odonto_proveedores')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('odonto_pacientes')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('odonto_doctores')->cascadeOnDelete();
            $table->date('fecha_pedido');
            $table->date('fecha_entrega_esperada')->nullable();
            $table->date('fecha_entrega_real')->nullable();
            $table->string('tipo_trabajo');
            $table->text('descripcion')->nullable();
            $table->string('color_protesis')->nullable();
            $table->decimal('costo', 10, 2)->default(0);
            $table->enum('estado', ['pendiente','en_proceso','listo','entregado','cancelado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_pedidos_laboratorio'); }
};
