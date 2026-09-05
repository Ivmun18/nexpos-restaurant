<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE caja_restaurante MODIFY tipo_comprobante ENUM('boleta','factura','ninguno','ticket') NOT NULL DEFAULT 'ninguno'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE caja_restaurante MODIFY tipo_comprobante ENUM('boleta','factura','ninguno') NOT NULL DEFAULT 'ninguno'");
    }
};
