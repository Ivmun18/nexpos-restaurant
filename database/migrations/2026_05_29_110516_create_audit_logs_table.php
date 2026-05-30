<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->string('action', 20);  // created, updated, deleted
            $table->string('model', 100);  // ActoNotarial, ComprobanteSunat, etc
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('record_label', 255)->nullable(); // EXP-2026-00001, B001-00000001
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['empresa_id', 'created_at']);
            $table->index(['model', 'record_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
