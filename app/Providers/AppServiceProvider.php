<?php

namespace App\Providers;

use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Relation::enforceMorphMap([
            'user' => User::class,
            'anime' => Anime::class,
            'manga' => Manga::class,
            'music' => MusicTrack::class,
            'game' => Game::class,
            'post' => Post::class,
            'episode' => Episode::class,
            'chapter' => Chapter::class,
            Anime::class => Anime::class,
            Manga::class => Manga::class,
            MusicTrack::class => MusicTrack::class,
            Game::class => Game::class,
            Episode::class => Episode::class,
            Chapter::class => Chapter::class,
        ]);
    }
}
