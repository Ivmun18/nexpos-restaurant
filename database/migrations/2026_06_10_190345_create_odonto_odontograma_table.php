<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('odonto_odontograma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('paciente_id')->constrained('odonto_pacientes');
            $table->integer('diente'); // FDI: 11-48
            $table->enum('estado', ['sano','caries','tratamiento','extraccion','ausente','corona','implante','sellante'])->default('sano');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->unique(['paciente_id','diente']);
        });
    }
    public function down() {
        Schema::dropIfExists('odonto_odontograma');
    }
};
