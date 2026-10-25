<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CatalogService
{
    public const TYPES = [
        'anime' => Anime::class,
        'manga' => Manga::class,
        'music' => MusicTrack::class,
        'game' => Game::class,
    ];

    public function model(string $type): string
    {
        if (! isset(self::TYPES[$type])) {
            throw new InvalidArgumentException("Tipo de contenido no soportado: {$type}");
        }

        return self::TYPES[$type];
    }

    public function paginate(string $type, int $perPage = 12): LengthAwarePaginator
    {
        return $this->model($type)::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findBySlug(string $type, string $slug): Model
    {
        return $this->model($type)::query()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        return [
            'anime' => Anime::count(),
            'manga' => Manga::count(),
            'music' => MusicTrack::count(),
            'games' => Game::count(),
        ];
    }
}
