<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('odonto_presupuestos')->cascadeOnDelete();
            $table->foreignId('tratamiento_id')->nullable()->constrained('odonto_tratamientos_catalogo')->nullOnDelete();
            $table->integer('numero_pieza')->nullable();
            $table->string('descripcion');
            $table->decimal('precio', 10, 2);
            $table->integer('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->enum('estado_item', ['pendiente','en_proceso','completado','cancelado'])->default('pendiente');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_presupuesto_items'); }
};
