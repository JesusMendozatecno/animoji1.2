<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * 🎭 Personaje: identidad visual (color + imagen + fondo) integrada
 * con el sistema de temas de login/index. `directory` es una carpeta
 * segura, única y derivada del nombre del tema.
 */
class Character extends Model
{
    protected $fillable = [
        'theme_name',
        'directory',
        'character_name',
        'color_hex',
        'image_path',
        'background_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Carpeta segura derivada del nombre del tema (sin traversal ni
     * caracteres peligrosos). Colisiones: sufijo numérico único.
     */
    public static function safeDirectory(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-');
        $base = $base !== '' ? $base : 'personaje';

        $candidate = $base;
        $n = 2;

        while (self::query()
            ->where('directory', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$n++;
        }

        return $candidate;
    }
}
