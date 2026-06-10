<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('odonto_pagos', function (Blueprint $table) {
            $table->enum('tipo_movimiento', ['adelanto','pago','cancelacion'])->default('pago')->after('tipo_pago');
            $table->decimal('saldo_pendiente', 10, 2)->default(0)->after('monto_total');
        });
    }
    public function down() {
        Schema::table('odonto_pagos', function (Blueprint $table) {
            $table->dropColumn(['tipo_movimiento','saldo_pendiente']);
        });
    }
};
