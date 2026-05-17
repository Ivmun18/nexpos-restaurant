<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado ENUM('borrador','pendiente','emitido','aceptado','rechazado','anulado') NOT NULL DEFAULT 'emitido'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN estado ENUM('borrador','emitido','aceptado','rechazado','anulado') NOT NULL DEFAULT 'emitido'");
    }
};
