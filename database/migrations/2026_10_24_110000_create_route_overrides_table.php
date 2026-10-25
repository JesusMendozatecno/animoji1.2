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
        Schema::create('route_overrides', function (Blueprint $table) {
            $table->id();

            // Identificador del botón/sección: 'header.iniciar_sesion', 'index.mi_lista', ...
            $table->string('key', 80)->unique();

            // Destino elegido en Direcciones (ruta del sistema, ej. '/anime')
            $table->string('path', 255);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_overrides');
    }
};
