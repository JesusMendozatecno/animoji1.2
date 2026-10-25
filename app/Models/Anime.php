<?php

namespace App\Models;

use App\Models\Concerns\HasCommunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anime extends Model
{
    use HasCommunity;

    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'cover',
        'studio',
        'year',
        'status',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'year' => 'integer',
        ];
    }

    /** Episodios en orden narrativo/numérico (temporada, episodio). */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)
            ->orderBy('season_number')
            ->orderBy('episode_number');
    }
}
