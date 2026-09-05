<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE comprobantes_sunat MODIFY estado ENUM('emitido','aceptado','rechazado','anulado','pendiente','ticket') NOT NULL DEFAULT 'emitido'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE comprobantes_sunat MODIFY estado ENUM('emitido','aceptado','rechazado','anulado','pendiente') NOT NULL DEFAULT 'emitido'");
    }
};
