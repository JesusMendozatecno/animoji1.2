<?php

namespace App\Models;

use App\Models\Concerns\HasCommunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manga extends Model
{
    use HasCommunity;

    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'author',
        'artist',
        'cover',
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

    /** Capítulos en orden numérico real (no alfabético). */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('chapter_number');
    }
}
