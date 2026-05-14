<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acto_requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acto_id')->constrained('actos_notariales')->onDelete('cascade');
            $table->string('documento', 200);
            $table->boolean('entregado')->default(false);
            $table->string('observacion', 300)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acto_requisitos');
    }
};
