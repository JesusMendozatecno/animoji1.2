<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 📺 Episodios de anime.
 * Cada episodio pertenece a un anime y a una temporada/número concretos.
 * El progreso de cada usuario se guarda en media_progress (morph → Episode).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('season_number')->default(1);
            $table->unsignedSmallInteger('episode_number');
            $table->string('title')->nullable();
            $table->text('synopsis')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Segundos');
            $table->string('thumbnail')->nullable();
            $table->string('video_url')->nullable()->comment('Fuente de vídeo autorizada');
            $table->string('status')->default('published')->comment('published|draft');
            $table->timestamps();

            $table->unique(['anime_id', 'season_number', 'episode_number'], 'episodes_unique');
            $table->index(['anime_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
