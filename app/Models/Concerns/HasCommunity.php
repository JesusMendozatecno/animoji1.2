<?php

namespace App\Models\Concerns;

use App\Models\Favorite;
use App\Models\MediaProgress;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCommunity
{
    public function comments(): MorphMany
    {
        return $this->morphMany(\App\Models\Comment::class, 'commentable');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(MediaProgress::class, 'progressable');
    }
}
