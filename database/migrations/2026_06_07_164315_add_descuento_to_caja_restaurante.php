<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->decimal('descuento', 10, 2)->default(0)->after('vuelto');
        });
    }

    public function down(): void
    {
        Schema::table('caja_restaurante', function (Blueprint $table) {
            $table->dropColumn('descuento');
        });
    }
};
