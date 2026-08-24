<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->json('modificadores')->nullable()->after('notas');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropColumn('modificadores');
        });
    }
};
