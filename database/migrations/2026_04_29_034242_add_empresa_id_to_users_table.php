<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->default(1)->after('id');
            $table->string('rol', 20)->default('admin')->after('email');
        });

        // Actualizar usuario existente
        DB::table('users')->update(['empresa_id' => 1, 'rol' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['empresa_id', 'rol']);
        });
    }
};
