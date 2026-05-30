<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comprobantes_sunat', function (Blueprint $table) {
            $table->unsignedBigInteger('acto_id')->nullable()->after('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::table('comprobantes_sunat', function (Blueprint $table) {
            $table->dropColumn('acto_id');
        });
    }
};
