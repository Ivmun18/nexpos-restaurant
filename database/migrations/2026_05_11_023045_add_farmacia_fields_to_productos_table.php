<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('lote')->nullable()->after('fecha_vencimiento');
            $table->string('laboratorio')->nullable()->after('lote');
            $table->string('principio_activo')->nullable()->after('laboratorio');
            $table->string('presentacion')->nullable()->after('principio_activo');
            $table->string('concentracion')->nullable()->after('presentacion');
            $table->boolean('requiere_receta')->default(false)->after('concentracion');
        });
    }
    public function down(): void {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['lote','laboratorio','principio_activo','presentacion','concentracion','requiere_receta']);
        });
    }
};
