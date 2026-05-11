<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('productos', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->after('stock_actual');
            $table->integer('dias_alerta_vencimiento')->default(30)->after('fecha_vencimiento');
        });
    }
    public function down(): void {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['fecha_vencimiento', 'dias_alerta_vencimiento']);
        });
    }
};
