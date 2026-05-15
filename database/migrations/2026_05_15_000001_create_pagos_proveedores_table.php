<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos_proveedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('cuenta_por_pagar_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->string('numero_comprobante')->nullable();
            $table->enum('forma_pago', ['efectivo', 'transferencia', 'cheque', 'tarjeta'])->default('efectivo');
            $table->text('referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('cuenta_por_pagar_id')->references('id')->on('cuentas_por_pagar')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_proveedores');
    }
};