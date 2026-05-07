<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('metodo_pago')->nullable()->after('forma_pago');
            $table->string('nubefact_id')->nullable()->after('observaciones');
            $table->string('nubefact_estado')->nullable()->after('nubefact_id');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'nubefact_id', 'nubefact_estado']);
        });
    }
};
