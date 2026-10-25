<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** 🎫 Solicitud de soporte técnico con código de seguimiento. */
class SupportRequest extends Model
{
    public const STATUSES = ['open', 'answered', 'closed'];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'category',
        'subject',
        'description',
        'status',
        'tracking_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Genera un código de seguimiento único, legible y no adivinable. */
    public static function generateTrackingCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}
