<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('progressable');
            $table->unsignedBigInteger('position')->default(0)->comment('Segundo, página o minuto');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'progressable_type', 'progressable_id'], 'media_progress_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_progress');
    }
};
