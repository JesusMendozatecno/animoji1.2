<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⏳ Pendientes: contenido que el usuario quiere ver/leer/escuchar luego.
 * Límite de 10 por usuario Y por sección, validado en el servidor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('section', 16)->comment('anime|manga|musica|juegos');
            $table->morphs('pendingable');
            $table->timestamps();

            $table->unique(
                ['user_id', 'section', 'pendingable_type', 'pendingable_id'],
                'pending_items_unique',
            );
            $table->index(['user_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_items');
    }
};
