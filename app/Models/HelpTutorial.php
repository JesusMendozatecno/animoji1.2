<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 🎬 Tutorial en vídeo (fuente autorizada validada en servidor). */
class HelpTutorial extends Model
{
    protected $fillable = [
        'title',
        'module',
        'description',
        'cover',
        'video_url',
        'duration_minutes',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
