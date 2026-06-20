<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_fichas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('fecha');
            // Ojo derecho (OD)
            $table->decimal('od_esfera', 5, 2)->nullable();
            $table->decimal('od_cilindro', 5, 2)->nullable();
            $table->integer('od_eje')->nullable();
            $table->decimal('od_adicion', 5, 2)->nullable();
            $table->string('od_av', 20)->nullable();
            // Ojo izquierdo (OI)
            $table->decimal('oi_esfera', 5, 2)->nullable();
            $table->decimal('oi_cilindro', 5, 2)->nullable();
            $table->integer('oi_eje')->nullable();
            $table->decimal('oi_adicion', 5, 2)->nullable();
            $table->string('oi_av', 20)->nullable();
            // General
            $table->decimal('div', 5, 2)->nullable()->comment('Distancia interpupilar');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['empresa_id', 'paciente_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('optica_fichas');
    }
};
