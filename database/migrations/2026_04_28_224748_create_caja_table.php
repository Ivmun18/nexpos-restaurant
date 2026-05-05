<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('codigo', 10);
            $table->string('nombre', 80);
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
        });

        // Caja por defecto
        DB::table('caja')->insert([
            'empresa_id'     => 1,
            'codigo'         => 'CAJA01',
            'nombre'         => 'Caja Principal',
            'activo'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};