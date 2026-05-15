<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // PASO 1: Cambiar tipo de columna usando SQL crudo (sin doctrine/dbal)
        DB::statement("ALTER TABLE empresas MODIFY COLUMN regimen_tributario VARCHAR(20) NULL DEFAULT 'General'");

        // PASO 2: Convertir valores numericos a texto (ahora si acepta texto)
        DB::table('empresas')->where('regimen_tributario', '1')->update(['regimen_tributario' => 'General']);
        DB::table('empresas')->where('regimen_tributario', '2')->update(['regimen_tributario' => 'Especial']);
        DB::table('empresas')->where('regimen_tributario', '3')->update(['regimen_tributario' => 'MYPE']);
        DB::table('empresas')->where('regimen_tributario', '4')->update(['regimen_tributario' => 'RUS']);
    }

    public function down(): void {
        DB::table('empresas')->where('regimen_tributario', 'General')->update(['regimen_tributario' => '1']);
        DB::table('empresas')->where('regimen_tributario', 'Especial')->update(['regimen_tributario' => '2']);
        DB::table('empresas')->where('regimen_tributario', 'MYPE')->update(['regimen_tributario' => '3']);
        DB::table('empresas')->where('regimen_tributario', 'RUS')->update(['regimen_tributario' => '4']);

        DB::statement("ALTER TABLE empresas MODIFY COLUMN regimen_tributario TINYINT NOT NULL DEFAULT 1");
    }
};
