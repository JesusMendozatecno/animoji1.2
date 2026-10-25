<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\MangaResource;
use App\Http\Resources\MusicTrackResource;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function anime(): AnonymousResourceCollection
    {
        return AnimeResource::collection(Anime::query()->latest()->paginate(20));
    }

    public function animeShow(Anime $anime): AnimeResource
    {
        return new AnimeResource($anime);
    }

    public function manga(): AnonymousResourceCollection
    {
        return MangaResource::collection(Manga::query()->latest()->paginate(20));
    }

    public function mangaShow(Manga $manga): MangaResource
    {
        return new MangaResource($manga);
    }

    public function music(): AnonymousResourceCollection
    {
        return MusicTrackResource::collection(MusicTrack::query()->latest()->paginate(20));
    }

    public function musicShow(MusicTrack $track): MusicTrackResource
    {
        return new MusicTrackResource($track);
    }

    public function games(): AnonymousResourceCollection
    {
        return GameResource::collection(Game::query()->latest()->paginate(20));
    }

    public function gameShow(Game $game): GameResource
    {
        return new GameResource($game);
    }
}
