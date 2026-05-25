<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->boolean('pagado')->default(false)->after('estado');
            $table->unsignedBigInteger('caja_detalle_id')->nullable()->after('pagado');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropColumn(['pagado', 'caja_detalle_id']);
        });
    }
};
