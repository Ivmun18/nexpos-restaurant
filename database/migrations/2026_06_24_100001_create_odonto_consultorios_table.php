<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('odonto_consultorios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre');
            $table->string('color', 7)->default('#8B5CF6');
            $table->integer('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('empresa_id');
        });
    }
    public function down(): void { Schema::dropIfExists('odonto_consultorios'); }
};
