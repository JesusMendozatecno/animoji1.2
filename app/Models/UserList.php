<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 📋 Lista personal del usuario ("Mi lista"): privada por defecto,
 * restringida a una sección de contenido (anime|manga|musica|juegos).
 */
class UserList extends Model
{
    public const SECTIONS = ['anime', 'manga', 'musica', 'juegos'];

    protected $fillable = [
        'user_id',
        'section',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ListItem::class)->orderBy('position');
    }
}
