<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('actos_notariales', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_biometricos')->nullable()->default(1)->after('monto_cobrar');
        });
    }

    public function down(): void {
        Schema::table('actos_notariales', function (Blueprint $table) {
            $table->dropColumn('cantidad_biometricos');
        });
    }
};
