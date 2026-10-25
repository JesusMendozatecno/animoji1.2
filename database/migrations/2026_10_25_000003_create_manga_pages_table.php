<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 📄 Páginas de un capítulo de manga (imagen + orden).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->unsignedSmallInteger('page_number');
            $table->string('image_path');
            $table->timestamps();

            $table->unique(['chapter_id', 'page_number'], 'manga_pages_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_pages');
    }
};
