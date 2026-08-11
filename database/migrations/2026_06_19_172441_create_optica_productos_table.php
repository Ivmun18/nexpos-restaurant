<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->enum('categoria', ['luna', 'montura', 'lente_contacto', 'solucion', 'accesorio', 'otro'])->default('otro');
            $table->string('marca')->nullable();
            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(2);
            $table->string('unidad')->default('und');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('empresa_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_productos');
    }
};
