<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 🕵️ Evento de auditoría del sistema. Escritura SOLO desde el servidor
 * (AuditLogger); la interfaz únicamente consulta con permiso.
 */
class AuditEvent extends Model
{
    public const RESULTS = ['success', 'failed', 'blocked'];

    protected $fillable = [
        'user_id',
        'event_type',
        'module',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'result',
        'ip_address',
        'correlation_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'subject_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
