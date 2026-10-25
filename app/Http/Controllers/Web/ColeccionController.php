<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Admin\CustomizationController;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Services\CatalogService;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColeccionController extends Controller
{
    public function __invoke(Request $request, CatalogService $catalog): View
    {
        // Collect a bounded set from each catalog, then globally sort the merged results.
        $recent = collect([
            ...Anime::query()->latest('created_at')->limit(4)->get()->map(fn ($item) => [
                'title' => $item->title,
                'type' => 'Anime',
                'cover' => $item->cover,
                'slug' => $item->slug,
                'route' => 'anime.show',
                'created_at' => $item->created_at,
            ]),
            ...Manga::query()->latest('created_at')->limit(4)->get()->map(fn ($item) => [
                'title' => $item->title,
                'type' => 'Manga',
                'cover' => $item->cover,
                'slug' => $item->slug,
                'route' => 'manga.show',
                'created_at' => $item->created_at,
            ]),
            ...MusicTrack::query()->latest('created_at')->limit(4)->get()->map(fn ($item) => [
                'title' => $item->title,
                'type' => 'Música',
                'cover' => $item->cover,
                'slug' => $item->slug,
                'route' => 'music.show',
                'created_at' => $item->created_at,
            ]),
            ...Game::query()->latest('created_at')->limit(4)->get()->map(fn ($item) => [
                'title' => $item->title,
                'type' => 'Videojuegos',
                'cover' => $item->cover,
                'slug' => $item->slug,
                'route' => 'games.show',
                'created_at' => $item->created_at,
            ]),
        ])
            ->sortByDesc('created_at')
            ->take(4)
            ->values()
            ->map(fn (array $item) => collect($item)->except('created_at')->all())
            ->all();

        return view('coleccion', [
            'user' => $request->user(),
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $request->user()?->customizing,
            'stats' => $catalog->counts(),
            'recent' => $recent,
        ]);
    }
}
