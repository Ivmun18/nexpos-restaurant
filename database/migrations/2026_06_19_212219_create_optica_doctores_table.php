<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('optica_doctores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre');
            $table->string('especialidad')->nullable();
            $table->string('colegiatura', 30)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('empresa_id');
        });
        Schema::table('optica_fichas', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('user_id');
        });
    }
    public function down(): void {
        Schema::table('optica_fichas', function (Blueprint $table) {
            $table->dropColumn('doctor_id');
        });
        Schema::dropIfExists('optica_doctores');
    }
};
