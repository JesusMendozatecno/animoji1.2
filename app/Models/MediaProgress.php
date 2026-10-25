<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaProgress extends Model
{
    protected $fillable = [
        'user_id',
        'progressable_type',
        'progressable_id',
        'position',
        'percentage',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'percentage' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progressable(): MorphTo
    {
        return $this->morphTo();
    }
}
