<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 📺 Episodio de anime (temporada + número + fuente de vídeo).
 * El progreso de cada usuario se guarda en media_progress (polimórfico).
 */
class Episode extends Model
{
    protected $fillable = [
        'anime_id',
        'season_number',
        'episode_number',
        'title',
        'synopsis',
        'duration',
        'thumbnail',
        'video_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'season_number' => 'integer',
            'episode_number' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(MediaProgress::class, 'progressable');
    }

    /** Etiqueta corta: T1 · E2 */
    public function getLabelAttribute(): string
    {
        return 'T'.$this->season_number.' · E'.$this->episode_number;
    }
}
