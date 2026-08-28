<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comanda_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('mesa_id')->nullable();
            $table->string('mesa_nombre')->nullable();
            $table->unsignedBigInteger('mozo_id')->nullable();
            $table->string('mozo_nombre')->nullable();
            $table->json('items');
            $table->timestamp('created_at')->nullable();

            $table->index(['empresa_id', 'created_at']);
            $table->index('sucursal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comanda_logs');
    }
};
