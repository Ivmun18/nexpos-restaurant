<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->json('pagos')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->dropColumn('pagos');
        });
    }
};
