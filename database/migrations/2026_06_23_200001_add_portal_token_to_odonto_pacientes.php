<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('odonto_pacientes', function (Blueprint $table) {
            $table->string('portal_token', 64)->nullable()->unique()->after('activo');
        });
    }
    public function down(): void {
        Schema::table('odonto_pacientes', function (Blueprint $table) {
            $table->dropColumn('portal_token');
        });
    }
};
