<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('compra_detalle', function (Blueprint $table) {
            if (!Schema::hasColumn('compra_detalle', 'lote')) {
                $table->string('lote', 50)->nullable()->after('cantidad');
            }
            if (!Schema::hasColumn('compra_detalle', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('lote');
            }
        });
    }

    public function down(): void {
        Schema::table('compra_detalle', function (Blueprint $table) {
            $table->dropColumn(['lote', 'fecha_vencimiento']);
        });
    }
};
