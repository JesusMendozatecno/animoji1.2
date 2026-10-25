<?php

namespace App\Models;

use App\Models\Concerns\HasCommunity;
use Illuminate\Database\Eloquent\Model;

class MusicTrack extends Model
{
    use HasCommunity;

    protected $fillable = [
        'title',
        'slug',
        'artist',
        'album',
        'genre',
        'cover',
        'audio_url',
        'duration',
        'release_year',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'release_year' => 'integer',
        ];
    }
}
