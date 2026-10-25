<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('commentable');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->text('body');
            $table->string('status')->default('published');
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id', 'created_at'], 'comments_target_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
