<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('proveedor_facturacion')->default('apisunat')->after('regimen_tributario');
        });
    }
    public function down(): void {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('proveedor_facturacion');
        });
    }
};
