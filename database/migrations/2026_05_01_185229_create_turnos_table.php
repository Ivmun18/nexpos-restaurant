<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['mañana', 'tarde', 'noche', 'personalizado'])->default('personalizado');
            $table->string('nombre')->nullable(); // nombre personalizado
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamp('apertura')->useCurrent();
            $table->timestamp('cierre')->nullable();
            $table->decimal('total_ventas', 10, 2)->default(0);
            $table->integer('total_mesas')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};