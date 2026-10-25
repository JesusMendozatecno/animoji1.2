<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 📚 Manual de usuario (publicado, borrador o archivado). */
class HelpManual extends Model
{
    protected $fillable = [
        'title',
        'module',
        'summary',
        'content',
        'attachment',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
