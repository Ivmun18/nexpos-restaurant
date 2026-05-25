<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->boolean('anulado')->default(false)->after('pagado');
            $table->string('motivo_anulacion')->nullable()->after('anulado');
            $table->unsignedBigInteger('anulado_por')->nullable()->after('motivo_anulacion');
            $table->timestamp('anulado_at')->nullable()->after('anulado_por');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropColumn(['anulado', 'motivo_anulacion', 'anulado_por', 'anulado_at']);
        });
    }
};
