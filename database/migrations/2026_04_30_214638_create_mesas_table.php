<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('numero', 10);
            $table->string('nombre', 50)->nullable();
            $table->unsignedTinyInteger('capacidad')->default(4);
            $table->enum('zona', ['salon', 'terraza', 'barra', 'privado', 'delivery'])->default('salon');
            $table->enum('estado', ['libre', 'ocupada', 'reservada', 'bloqueada'])->default('libre');
            $table->unsignedBigInteger('mozo_id')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'numero']);
            $table->foreign('mozo_id')->references('id')->on('users')->nullOnDelete();
        });

        // Mesas por defecto
        $mesas = [];
        for ($i = 1; $i <= 12; $i++) {
            $mesas[] = [
                'empresa_id' => 1,
                'numero'     => (string) $i,
                'nombre'     => 'Mesa ' . $i,
                'capacidad'  => 4,
                'zona'       => $i <= 8 ? 'salon' : 'terraza',
                'estado'     => 'libre',
                'orden'      => $i,
                'activo'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('mesas')->insert($mesas);
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};