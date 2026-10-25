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
        Schema::table('users', function (Blueprint $table) {
            // Modo "Personalización de sistema" activo para este admin:
            // al activarlo, todas las zonas de imágenes muestran el botón "+ Agregar imagen".
            $table->boolean('customizing')->default(false)->after('index_theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('customizing');
        });
    }
};
