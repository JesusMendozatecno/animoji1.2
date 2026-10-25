<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Imagen personalizada de una zona del sistema (sidebar, hero, ...).
 * La guarda el admin desde "Personalización de sistema".
 */
class SystemImage extends Model
{
    protected $fillable = [
        'zone',
        'image_path',
        'pos_x',
        'pos_y',
        'scale',
    ];

    protected function casts(): array
    {
        return [
            'pos_x' => 'integer',
            'pos_y' => 'integer',
            'scale' => 'float',
        ];
    }
}
