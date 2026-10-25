<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ⏳ Pendiente: contenido reservado para ver/leer/escuchar después.
 * Límite de 10 por usuario y sección (comprobado y bloqueado en servidor).
 */
class PendingItem extends Model
{
    public const LIMIT = 10;

    protected $fillable = [
        'user_id',
        'section',
        'pendingable_type',
        'pendingable_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pendingable(): MorphTo
    {
        return $this->morphTo();
    }
}
