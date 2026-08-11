<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_id');
            $table->unsignedBigInteger('empresa_id');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->string('referencia')->nullable()->comment('venta_id u otro');
            $table->timestamps();
            $table->index('caja_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_caja_movimientos');
    }
};
