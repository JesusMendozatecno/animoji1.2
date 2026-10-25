<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 📖 Capítulo de manga con sus páginas ordenadas.
 */
class Chapter extends Model
{
    protected $fillable = [
        'manga_id',
        'volume_number',
        'chapter_number',
        'title',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'volume_number' => 'integer',
            'chapter_number' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(MangaPage::class)->orderBy('page_number');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(MediaProgress::class, 'progressable');
    }
}
