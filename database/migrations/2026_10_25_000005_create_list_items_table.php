<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ➕ Elementos de una lista personal (polimórfico y restringido a la
 * sección de la lista por validación en el servidor).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_list_id')->constrained('user_lists')->cascadeOnDelete();
            $table->morphs('itemable');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(
                ['user_list_id', 'itemable_type', 'itemable_id'],
                'list_items_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_items');
    }
};
