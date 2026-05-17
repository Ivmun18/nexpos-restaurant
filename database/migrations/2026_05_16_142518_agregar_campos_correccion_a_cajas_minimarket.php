<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas_minimarket', function (Blueprint $table) {
            $table->decimal('monto_inicial_original', 10, 2)->nullable()->after('monto_inicial');
            $table->decimal('monto_final_original', 10, 2)->nullable()->after('monto_final');
            $table->text('motivo_correccion')->nullable()->after('observaciones');
            $table->unsignedBigInteger('corregido_por_id')->nullable()->after('motivo_correccion');
            $table->timestamp('corregido_at')->nullable()->after('corregido_por_id');

            $table->foreign('corregido_por_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cajas_minimarket', function (Blueprint $table) {
            $table->dropForeign(['corregido_por_id']);
            $table->dropColumn([
                'monto_inicial_original',
                'monto_final_original',
                'motivo_correccion',
                'corregido_por_id',
                'corregido_at',
            ]);
        });
    }
};
