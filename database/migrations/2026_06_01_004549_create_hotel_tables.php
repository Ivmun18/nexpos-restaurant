<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tipos de habitación
        Schema::create('hotel_tipos_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nombre'); // Simple, Doble, Suite, etc.
            $table->text('descripcion')->nullable();
            $table->decimal('precio_noche', 10, 2);
            $table->integer('capacidad')->default(2);
            $table->json('comodidades')->nullable(); // wifi, tv, ac, etc.
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Habitaciones
        Schema::create('hotel_habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('tipo_id')->constrained('hotel_tipos_habitacion')->onDelete('cascade');
            $table->string('numero'); // 101, 102, etc.
            $table->integer('piso')->default(1);
            $table->enum('estado', ['disponible', 'ocupada', 'limpieza', 'mantenimiento'])->default('disponible');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Huéspedes
        Schema::create('hotel_huespedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('tipo_documento', 2)->default('1'); // 1=DNI, 6=RUC
            $table->string('numero_documento', 15);
            $table->string('nombre_completo');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('nacionalidad')->nullable()->default('Peruana');
            $table->string('procedencia')->nullable();
            $table->timestamps();
        });

        // Reservas / Check-in
        Schema::create('hotel_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('habitacion_id')->constrained('hotel_habitaciones')->onDelete('cascade');
            $table->foreignId('huesped_id')->constrained('hotel_huespedes')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('codigo')->unique(); // RES-2026-001
            $table->dateTime('fecha_checkin');
            $table->dateTime('fecha_checkout_previsto');
            $table->dateTime('fecha_checkout_real')->nullable();
            $table->integer('num_huespedes')->default(1);
            $table->decimal('precio_noche', 10, 2);
            $table->integer('num_noches')->default(1);
            $table->decimal('total', 10, 2);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->enum('estado', ['reservado', 'checkin', 'checkout', 'cancelado'])->default('reservado');
            $table->enum('estado_pago', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->string('metodo_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Pagos de reservas
        Schema::create('hotel_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('hotel_reservas')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago')->default('efectivo');
            $table->string('referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Housekeeping
        Schema::create('hotel_housekeeping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('habitacion_id')->constrained('hotel_habitaciones')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_housekeeping');
        Schema::dropIfExists('hotel_pagos');
        Schema::dropIfExists('hotel_reservas');
        Schema::dropIfExists('hotel_huespedes');
        Schema::dropIfExists('hotel_habitaciones');
        Schema::dropIfExists('hotel_tipos_habitacion');
    }
};
