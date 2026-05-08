<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('cliente_tipo_doc', 20)->change();
            $table->string('cliente_num_doc', 20)->change();
        });
    }
    public function down(): void {}
};
