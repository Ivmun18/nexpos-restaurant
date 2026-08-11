<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre');
            $table->string('color', 30)->default('gray');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('empresa_id');
        });

        // Agregar columna categoria_id a optica_productos
        Schema::table('optica_productos', function (Blueprint $table) {
            $table->unsignedBigInteger('categoria_id')->nullable()->after('categoria');
        });
    }
    public function down(): void {
        Schema::table('optica_productos', function (Blueprint $table) {
            $table->dropColumn('categoria_id');
        });
        Schema::dropIfExists('optica_categorias');
    }
};
