<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('codigo', 50);
            $table->string('codigo_sunat', 20)->nullable();
            $table->string('codigo_barras', 50)->nullable();
            $table->string('descripcion', 500);
            $table->string('descripcion_corta', 100)->nullable();
            $table->enum('tipo', ['producto','servicio','combo'])->default('producto');
            $table->string('unidad_medida', 10)->default('NIU');
            $table->decimal('precio_venta', 12, 4)->default(0);
            $table->decimal('precio_compra', 12, 4)->nullable();
            $table->decimal('precio_minimo', 12, 4)->nullable();
            $table->boolean('afecto_igv')->default(true);
            $table->boolean('afecto_isc')->default(false);
            $table->boolean('afecto_icbper')->default(false);
            $table->string('tipo_afectacion_igv', 2)->default('10');
            $table->decimal('tasa_isc', 5, 2)->nullable();
            $table->boolean('controla_stock')->default(true);
            $table->decimal('stock_minimo', 12, 3)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
