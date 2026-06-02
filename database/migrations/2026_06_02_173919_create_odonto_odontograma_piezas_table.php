<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_odontograma_piezas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontograma_id')->constrained('odonto_odontograma')->cascadeOnDelete();
            $table->integer('numero_pieza');
            $table->string('estado')->nullable();
            $table->string('cara_mesial')->nullable();
            $table->string('cara_distal')->nullable();
            $table->string('cara_oclusal')->nullable();
            $table->string('cara_vestibular')->nullable();
            $table->string('cara_palatino')->nullable();
            $table->string('color')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_odontograma_piezas'); }
};
