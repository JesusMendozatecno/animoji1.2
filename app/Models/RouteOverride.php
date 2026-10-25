<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ruta personalizada de un botón/sección del sistema.
 * Creada desde Administración → Personalización → Direcciones.
 */
class RouteOverride extends Model
{
    protected $fillable = [
        'key',
        'path',
    ];
}
