<?php

namespace App\Models;

use App\Models\Concerns\HasCommunity;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasCommunity;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover',
        'platforms',
        'requirements',
        'download_url',
        'release_year',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'platforms' => 'array',
            'rating' => 'decimal:1',
            'release_year' => 'integer',
        ];
    }
}
