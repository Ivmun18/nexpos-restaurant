<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->unsignedInteger('partes_total')->default(1)->after('total');
            $table->unsignedInteger('parte_numero')->default(1)->after('partes_total');
            $table->decimal('cuenta_total', 10, 2)->nullable()->after('parte_numero');
            $table->decimal('pagado_acumulado', 10, 2)->default(0)->after('cuenta_total');
        });
    }

    public function down(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->dropColumn(['partes_total', 'parte_numero', 'cuenta_total', 'pagado_acumulado']);
        });
    }
};
