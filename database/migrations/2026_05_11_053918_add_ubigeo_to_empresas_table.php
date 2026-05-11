<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('ubigeo')->nullable()->after('direccion');
            $table->string('distrito')->nullable()->after('ubigeo');
            $table->string('provincia')->nullable()->after('distrito');
            $table->string('departamento')->nullable()->after('provincia');
        });
    }
    public function down(): void {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['ubigeo','distrito','provincia','departamento']);
        });
    }
};
