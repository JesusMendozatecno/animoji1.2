<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('artist');
            $table->string('album')->nullable();
            $table->string('cover')->nullable();
            $table->string('audio_url')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Duración en segundos');
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_tracks');
    }
};
