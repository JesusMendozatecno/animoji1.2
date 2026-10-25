<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 📋 Listas personales del usuario ("Mi lista").
 * Privadas por defecto: solo las consulta su propietario.
 * `section` identifica el tipo de contenido que admite la lista
 * (anime|manga|musica|juegos), de modo que nunca se mezclan entidades.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('section', 16)->comment('anime|manga|musica|juegos');
            $table->string('name');
            $table->timestamps();

            $table->unique(['user_id', 'section', 'name'], 'user_lists_unique');
            $table->index(['user_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lists');
    }
};
