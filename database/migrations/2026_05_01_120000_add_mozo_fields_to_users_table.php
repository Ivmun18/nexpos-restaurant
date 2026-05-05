<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 20)->nullable()->after('rol');
            $table->string('telefono', 20)->nullable()->after('dni');
            $table->date('fecha_ingreso')->nullable()->after('telefono');
            $table->boolean('activo')->default(true)->after('fecha_ingreso');
            $table->text('observaciones')->nullable()->after('activo');

            $table->index(['empresa_id', 'rol', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'rol', 'activo']);
            $table->dropColumn(['dni', 'telefono', 'fecha_ingreso', 'activo', 'observaciones']);
        });
    }
};
