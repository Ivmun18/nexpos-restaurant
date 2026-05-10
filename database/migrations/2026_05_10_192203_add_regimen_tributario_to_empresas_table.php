<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('regimen_tributario')->default('GENERAL')->after('zona_exonerada');
            // Valores: GENERAL, RUS, RER, MYPE
        });
    }

    public function down(): void {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('regimen_tributario');
        });
    }
};
