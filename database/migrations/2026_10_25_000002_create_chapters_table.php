<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 📖 Capítulos de manga.
 * Orden numérico real por manga; las páginas viven en manga_pages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('volume_number')->nullable()->comment('Volumen si aplica');
            $table->unsignedSmallInteger('chapter_number');
            $table->string('title')->nullable();
            $table->string('status')->default('published')->comment('published|draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['manga_id', 'chapter_number'], 'chapters_unique');
            $table->index(['manga_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
