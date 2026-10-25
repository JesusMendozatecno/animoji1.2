<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🎵 Ampliación de canciones: género/categoría y estado de publicación
 * (los borradores no aparecen en el catálogo público).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_tracks', function (Blueprint $table) {
            $table->string('genre')->nullable()->after('album')->comment('Categoría/género musical');
            $table->string('status', 16)->default('published')->after('release_year')
                ->comment('published|draft');
        });
    }

    public function down(): void
    {
        Schema::table('music_tracks', function (Blueprint $table) {
            $table->dropColumn(['genre', 'status']);
        });
    }
};
