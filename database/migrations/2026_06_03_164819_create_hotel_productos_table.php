<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hotel_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->string('categoria', 50)->default('otros'); // minibar, room_service, lavanderia, otros
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('controla_stock')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('hotel_productos');
    }
};
