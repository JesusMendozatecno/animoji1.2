<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_images', function (Blueprint $table) {
            $table->id();

            // Identificador de la zona: 'admin.sidebar', 'home.hero', 'usuario.sidebar', ...
            $table->string('zone', 64)->unique();

            // Imagen elegida de la galería (ruta relativa a public/)
            $table->string('image_path', 255);

            // Ajuste de la imagen dentro de la zona (object-position + zoom)
            $table->unsignedTinyInteger('pos_x')->default(50); // 0-100 %
            $table->unsignedTinyInteger('pos_y')->default(50); // 0-100 %
            $table->decimal('scale', 5, 2)->default(1);        // 0.5 - 3

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_images');
    }
};
