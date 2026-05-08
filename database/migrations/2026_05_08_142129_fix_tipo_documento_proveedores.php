<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('tipo_documento', 20)->change();
            $table->string('numero_documento', 20)->change();
        });
    }
    public function down(): void {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->char('tipo_documento', 1)->change();
        });
    }
};
