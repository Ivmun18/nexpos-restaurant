<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE caja_restaurante DROP FOREIGN KEY caja_restaurante_mesa_id_foreign');
        DB::statement('ALTER TABLE caja_restaurante MODIFY mesa_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE caja_restaurante ADD CONSTRAINT caja_restaurante_mesa_id_foreign FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE caja_restaurante DROP FOREIGN KEY caja_restaurante_mesa_id_foreign');
        DB::statement('ALTER TABLE caja_restaurante MODIFY mesa_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE caja_restaurante ADD CONSTRAINT caja_restaurante_mesa_id_foreign FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE');
    }
};
