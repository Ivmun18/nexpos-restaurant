<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inventario_movimientos MODIFY COLUMN tipo ENUM('inicial','compra','venta','ajuste','merma','entrada','salida') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inventario_movimientos MODIFY COLUMN tipo ENUM('inicial','compra','venta','ajuste','merma') NOT NULL");
    }
};
