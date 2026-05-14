<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acto_datos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acto_id')->constrained('actos_notariales')->onDelete('cascade');
            $table->string('campo', 100);
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acto_datos');
    }
};
