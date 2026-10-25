<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🎭 Personajes: identidades visuales (color + ilustración + fondo)
 * integradas con el sistema de temas existente. Cada personaje se
 * materializa en una carpeta pública segura derivada de su nombre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name')->comment('Nombre del color/tema, ej. Rojo');
            $table->string('directory')->unique()->comment('Carpeta segura, ej. rojo');
            $table->string('character_name')->comment('Nombre del personaje');
            $table->string('color_hex', 7)->default('#ff263c');
            $table->string('image_path')->nullable()->comment('Imagen del personaje');
            $table->string('background_path')->nullable()->comment('Imagen de fondo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
