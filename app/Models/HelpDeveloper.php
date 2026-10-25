<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 🧑‍💻 Perfil de desarrollador mostrado en Ayuda (configurable por admin). */
class HelpDeveloper extends Model
{
    protected $fillable = [
        'name',
        'role',
        'bio',
        'specialty',
        'avatar',
        'email',
        'social_links',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }
}
