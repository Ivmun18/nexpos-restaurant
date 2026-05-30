<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->string('metodo_pago', 30)->nullable()->after('monto');
        });

        // Extraer método de pago del concepto existente
        DB::statement("
            UPDATE caja_movimientos
            SET metodo_pago = CASE
                WHEN concepto LIKE '%(efectivo)%' THEN 'efectivo'
                WHEN concepto LIKE '%(yape)%' THEN 'yape'
                WHEN concepto LIKE '%(plin)%' THEN 'plin'
                WHEN concepto LIKE '%(transferencia)%' THEN 'transferencia'
                WHEN concepto LIKE '%(tarjeta)%' THEN 'tarjeta'
                ELSE 'efectivo'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
    }
};
