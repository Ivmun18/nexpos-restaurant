<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('industry_type', 50)->default('restaurante')->after('ruc');
            $table->json('modules_enabled')->nullable()->after('industry_type');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['industry_type', 'modules_enabled']);
        });
    }
};
