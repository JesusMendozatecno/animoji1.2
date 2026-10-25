<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🕵️ Historial del sistema ("caja negra" de auditoría).
 * Solo escritura desde el servidor; nunca se aceptan eventos del navegador.
 * Nunca almacena contraseñas, tokens ni secretos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 64)->comment('login, crud, security, config...');
            $table->string('module', 64)->comment('auth, usuarios, galeria, biblioteca...');
            $table->string('action', 120)->comment('Acción ejecutada, legible');
            $table->text('description')->nullable()->comment('Resultado en lenguaje natural');
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('result', 16)->default('success')->comment('success|failed|blocked');
            $table->string('ip_address', 45)->nullable();
            $table->string('correlation_id', 36)->nullable()->comment('Agrupa eventos de una operación');
            $table->json('meta')->nullable()->comment('Datos técnicos seguros');
            $table->timestamps();

            $table->index(['module', 'result']);
            $table->index(['event_type']);
            $table->index(['created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
