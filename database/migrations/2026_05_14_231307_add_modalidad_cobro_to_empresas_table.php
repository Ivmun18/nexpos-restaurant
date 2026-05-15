<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'modalidad_cobro')) {
                $table->string('modalidad_cobro')->nullable()->default('directo')->after('regimen_tributario');
            }
        });
    }

    public function down(): void {
        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'modalidad_cobro')) {
                $table->dropColumn('modalidad_cobro');
            }
        });
    }
};
