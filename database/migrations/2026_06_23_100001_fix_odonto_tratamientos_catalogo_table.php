<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Originalmente esta migración hacía dropIfExists + create de la tabla,
// lo que falla porque odonto_presupuesto_items.tratamiento_id tiene una FK
// apuntando acá (ON DELETE SET NULL). Se reescribe como ALTER aditivo e
// idempotente: agrega solo las columnas que falten, sin tocar
// 'precio_base' (todavía usado por PresupuestoController::storeCatalogo())
// ni romper la FK existente. Así además queda reproducible desde cero en
// un ambiente nuevo, cosa que antes no pasaba (una migración duplicada
// posterior había dejado la tabla como un stub de solo id+timestamps).
return new class extends Migration {
    public function up(): void {
        Schema::table('odonto_tratamientos_catalogo', function (Blueprint $table) {
            if (! Schema::hasColumn('odonto_tratamientos_catalogo', 'codigo')) {
                $table->string('codigo')->nullable()->after('empresa_id');
            }
            if (! Schema::hasColumn('odonto_tratamientos_catalogo', 'precio')) {
                $table->decimal('precio', 10, 2)->default(0)->after('descripcion');
            }
            if (! Schema::hasColumn('odonto_tratamientos_catalogo', 'duracion_minutos')) {
                $table->integer('duracion_minutos')->default(30)->after('precio');
            }
        });
    }

    public function down(): void {
        Schema::table('odonto_tratamientos_catalogo', function (Blueprint $table) {
            if (Schema::hasColumn('odonto_tratamientos_catalogo', 'duracion_minutos')) {
                $table->dropColumn('duracion_minutos');
            }
            if (Schema::hasColumn('odonto_tratamientos_catalogo', 'precio')) {
                $table->dropColumn('precio');
            }
            if (Schema::hasColumn('odonto_tratamientos_catalogo', 'codigo')) {
                $table->dropColumn('codigo');
            }
        });
    }
};
