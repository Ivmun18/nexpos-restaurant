<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('puede_facturar')->default(false)->after('activo');
        });

        // Notaría (empresa_id 15): solo Cynthia, Karla y el notario (Alex Saul
        // Herrera Arias) están autorizados a emitir comprobantes.
        DB::table('users')
            ->where('empresa_id', 15)
            ->where(function ($q) {
                $q->where('name', 'like', '%CYNTHIA%')
                    ->orWhere('name', 'like', '%KARLA%')
                    ->orWhere('name', 'like', '%ALEX SAUL HERRERA ARIAS%');
            })
            ->update(['puede_facturar' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('puede_facturar');
        });
    }
};
