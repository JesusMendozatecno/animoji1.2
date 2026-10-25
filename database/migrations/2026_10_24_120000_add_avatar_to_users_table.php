<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade la columna `avatar` a `users` (migración aditiva, no destructiva).
 *
 * Guarda la ruta relativa (dentro de public/) de la foto de perfil
 * recortada del usuario, p. ej. "images/usuarios/lobo118.jpg".
 * NULL = el usuario aún no tiene foto propia → se usa el avatar
 * predeterminado del sistema (/images/cuenta/avatar.png).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 255)->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
